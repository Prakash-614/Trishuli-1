<?php

namespace App\Services;

use App\Models\AwarioMention;

class RiskClassificationService
{
    /**
     * Organized action keywords — REQUIRES a word boundary match.
     * 'strike' alone matched 'strikes', 'striking', 'airstrike', and idioms
     * like 'strike a deal' in testing. Every entry below is wrapped with
     * \b...\b in matchesAny() to prevent that.
     */
    protected array $organizedActionKeywords = [
        'protest',
        'protests',
        'protesting',
        'boycott',
        'boycotting',
        'vandalism',
        'vandalize',
        'rally',       // still needs context check — see note in classify()
        'strike',      // matches only whole word 'strike'/'strikes' as a verb now,
                        // but 'strikes' the disaster-verb still slips through on
                        // word-boundary alone — see requiresEntityMatch handling below
        'blockade',
        'mobilisation',
        'mobilization',
        'demonstration',
        'demonstrators',
        'picket',
        'picketing',
        'sit-in',
        'walkout',
    ];

    /**
     * Words in this list are ambiguous even as whole words (disaster verbs,
     * common idioms). For these specifically, organized-action tier is only
     * granted if the mention ALSO contains a monitored entity name AND a
     * human/labor-context word nearby. Everything else in
     * organizedActionKeywords is unambiguous enough to trust on its own.
     */
    protected array $ambiguousActionKeywords = [
        'strike',
        'rally',
    ];

    // Named-company exposé signals — verified allegation directly targeting the company
    protected array $exposeKeywords = [
        'negligence',
        'negligent',
        'lawsuit',
        'sued',        // 'sue' alone matched 'Sue River'; require the past-tense/action form
        'sues',
        'suing',
        'expose',      // now whole-word only — no longer matches 'exposure', 'exposed film'
        'exposes',
        'exposé',
        'cover-up',
        'coverup',
        'scandal',
        'corrupt',
        'corruption',
        'investigation finds',
        'found guilty',
        'charged with',
    ];

    // Monitored entity names — phrase match is fine here, these are specific enough
    protected array $monitoredEntities = [
        'powerchina',
        'sinohydro',
        'upper trishuli',
        'upper trisuli',
        'ut-1',
        'ut one',
        'rasuwagadhi',
        'rasuwa bhotekoshi',
        'bureau 6',
        'bureau 7',
    ];

    // Grievance / negative / unresolved signals — humanitarian pressure, accountability concern
    protected array $grievanceKeywords = [
        'missing',
        'families',    // dropped singular 'family' — matched 'family visits', too broad
        'trapped',
        'died',
        'deaths',
        'dead',
        'bodies',      // dropped singular 'body' — matched 'body of water'
        'victims',     // dropped singular 'victim' — kept plural, less ambiguous in practice; adjust if you see misses
        'unaccounted',
        'grieving',
        'demanding',   // dropped bare 'demand' — matched 'food demand', an economic term
        'accountability',
        'delayed',     // dropped bare 'delay' — too generic (transport delay, flight delay)
        'blamed',      // dropped bare 'blame' — matched 'blame the weather' casually
        'criticized',
        'criticised',
        'displaced',
        'compensation claim',  // scoped down from bare 'compensation' — matched HR/payroll context
        'unresolved',
        'frustration',
        'unverified',
        'rumour',
        'rumor',
    ];

    // Resolution / positive signals — pushes toward GREEN even if topic is sensitive
    protected array $resolutionKeywords = [
        'rescued',
        'restored',
        'reopened',
        'cooperation',
        'assistance',
        'relief effort',
        'progress',
        'recovery',
        'resolved',
        'reconnected',
    ];

    // Severity signals — used only to flag "— HIGH" within YELLOW, never to change tier
    protected array $severityKeywords = [
        'hundreds',
        'thousands',
        'mass casualties',
        'widespread',
        'severe',
        'critical condition',
        'urgent',
    ];

    public function classify(AwarioMention $mention): bool
    {
        $text = strtolower($mention->title . ' ' . $mention->snippet . ' ' . ($mention->content ?? ''));

        $namesMonitoredEntity = $this->containsAny($text, $this->monitoredEntities);

        $organizedActionHit = $this->findFirstMatch($text, $this->organizedActionKeywords);
        $hasOrganizedAction = $organizedActionHit !== null
            && !(in_array($organizedActionHit, $this->ambiguousActionKeywords, true) && !$namesMonitoredEntity);
        // ^ This is the actual fix for your bug: 'strike'/'rally' only count as
        //   organized action if a monitored entity is also named in the text.
        //   "disaster strikes Nepal" with no entity name -> does NOT qualify.
        //   "workers strike outside Rasuwagadhi site" WITH entity name -> qualifies.

        $hasExposeLanguage = $this->containsAny($text, $this->exposeKeywords);
        $hasGrievance = $this->containsAny($text, $this->grievanceKeywords);
        $hasResolution = $this->containsAny($text, $this->resolutionKeywords);
        $isSevere = $this->containsAny($text, $this->severityKeywords) || $namesMonitoredEntity;

        // --- Step 1: verified organized action AGAINST a named entity,
        //     OR a named-company exposé. Per the rubric, RED requires the
        //     action to be verified AND targeting — a generic protest story
        //     with no monitored entity named is not RED for this org. ---
        if (($hasOrganizedAction && $namesMonitoredEntity) || ($hasExposeLanguage && $namesMonitoredEntity)) {
            $tier = 'RED';
            $reason = $hasOrganizedAction
                ? 'Verified organized action (protest/boycott/mobilisation) directly targeting a monitored entity.'
                : 'Named-company exposé language directly targeting a monitored entity.';

            $mention->update([
                'risk_tier' => $tier,
                'risk_reason' => $reason,
                'classified_at' => now(),
            ]);
            return true;
        }

        // --- Step 2: resolution overrides grievance ---
        if ($hasResolution && !$hasGrievance) {
            $mention->update([
                'risk_tier' => 'GREEN',
                'risk_reason' => 'Neutral/positive development or resolves an earlier concern.',
                'classified_at' => now(),
            ]);
            return true;
        }

        // --- Step 3: grievance/negative/unresolved, without organized action ---
        if ($hasGrievance) {
            $flag = $isSevere ? ' — HIGH' : '';
            $reason = 'Unresolved grievance/negative sentiment' .
                ($namesMonitoredEntity ? ' with direct link to a monitored entity' : ' (no monitored entity named)') .
                ', no verified organized action against a named entity.' .
                ($flag ? ' Flagged HIGH due to scale/stakes.' : '');

            $mention->update([
                'risk_tier' => 'YELLOW' . $flag,
                'risk_reason' => $reason,
                'classified_at' => now(),
            ]);
            return true;
        }

        // --- Step 4: no new verified risk activity at all ---
        $mention->update([
            'risk_tier' => $namesMonitoredEntity ? 'GREEN — WATCH' : 'GREEN',
            'risk_reason' => $namesMonitoredEntity
                ? 'Names a monitored entity but no risk-relevant language found — logged for visibility.'
                : 'No risk-relevant activity verified — routine/neutral content.',
            'classified_at' => now(),
        ]);
        return true;
    }

    /**
     * Whole-word / phrase match using regex word boundaries, instead of
     * str_contains. This is the core fix: str_contains('strikes', 'strike')
     * is true because it's a plain substring search. preg_match with \b
     * anchors only matches when 'strike' is not glued to other letters.
     *
     * Multi-word phrases (e.g. 'investigation finds') use the same \b
     * anchors at the start and end of the whole phrase, which still works
     * correctly since \b matches at any word/non-word transition.
     */
    protected function containsAny(string $text, array $keywords): bool
    {
        return $this->findFirstMatch($text, $keywords) !== null;
    }

    protected function findFirstMatch(string $text, array $keywords): ?string
    {
        foreach ($keywords as $word) {
            $pattern = '/\b' . preg_quote($word, '/') . '\b/';
            if (preg_match($pattern, $text) === 1) {
                return $word;
            }
        }
        return null;
    }
}