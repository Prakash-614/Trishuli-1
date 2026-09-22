<?php

namespace App\Services;

use App\Models\AwarioMention;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RiskClassificationService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key');
    }

    public function classify(AwarioMention $mention): bool
    {
        $systemPrompt = <<<PROMPT
You are a corporate reputation risk analyst producing entries for a Social & Media Risk Monitoring Report on a hydropower company in Nepal.

Monitored entities: Upper Trishuli-1 Hydropower Project (UT-1), POWERCHINA, Power Construction Corporation of China, SINOHYDRO, Sinohydro Bureau 6, Sinohydro Bureau 7, Rasuwagadhi Hydropower Project, Rasuwa Bhotekoshi Hydroelectric Project

Risk tiers:
- GREEN (Normal): Routine news, factual reporting, low-engagement/non-escalatory content. Positive institutional support, recovery/reconnection news, constructive developments.
- YELLOW (Elevated): Rising negative comments, emotional family posts, unverified rumours, dissatisfaction, or emerging accountability concerns — humanitarian/family/missing-person narratives that are severe but NOT organized against a company.
- RED (Critical): Verified threats/organisation of protests, mainstream-media exposés naming a company, aggressive social-media mobilisation, or coordinated corporate targeting.

Modifiers (append to the base tier, do not use as a 4th tier):
- " — HIGH" on YELLOW: used when the item is unusually severe or close to tipping into RED (e.g. large casualty/missing-worker numbers, direct project name in the headline, prolonged unresolved crisis).
- " — WATCH" on GREEN or YELLOW: used when there is no new verified escalatory signal today, but the topic requires continued monitoring (e.g. a social-media sweep with no findings, or a company remaining a sensitive exposure without a new allegation).

Decision logic, applied in order:
1. Does this contain a VERIFIED call to protest/boycott/vandalism, or a mainstream-media exposé naming a monitored company with an allegation of wrongdoing? -> RED
2. Is there organized action (protest/boycott/mobilisation) but NOT verified or NOT targeting a monitored company? -> stays YELLOW, does not escalate to RED
3. Is it humanitarian/emotional but passive (families, grief, missing persons, displacement, compensation, DNA/identification backlog) and unresolved? -> YELLOW (add "— HIGH" if scale/stakes are large)
4. Does it name a monitored company/project but report no new allegation, just presence in an ongoing narrative (e.g. tunnel search continuing)? -> YELLOW, or YELLOW — HIGH WATCH if it is the company's core ongoing exposure
5. Is it neutral, positive, institutional support, or resolves/improves an earlier concern (e.g. reconnection, aid, positive investment messaging)? -> GREEN
6. No new verified activity found in the monitoring window at all (e.g. a social-media sweep with nothing found)? -> GREEN — WATCH

Write the "reason" the way this report writes Risk Interpretation: state what the item IS (humanitarian/operational/institutional/accusatory), whether it establishes corporate wrongdoing, and what it could plausibly escalate into.

Respond ONLY with valid JSON, no markdown formatting, no other text:
{"tier": "GREEN|YELLOW|RED", "modifier": "HIGH|WATCH|HIGH WATCH|null", "reason": "one to two sentences in the style of a Risk Interpretation", "summary": "1-2 sentence factual summary of the mention"}
PROMPT;

        $userContent = "Title: {$mention->title}\nSnippet: {$mention->snippet}\nSource: {$mention->source}\nReach: {$mention->reach}\nSentiment (auto-detected): {$mention->sentiment}\nPosted: {$mention->mentioned_at}";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-5.6-luna',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userContent],
            ],
            'response_format' => ['type' => 'json_object'],
            'max_completion_tokens' => 1500,
        ]);

        if (!$response->successful()) {
            Log::warning("OpenAI classify failed for mention #{$mention->id}", [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            return false;
        }

        $text = $response->json('choices.0.message.content');
        $parsed = $text ? json_decode($text, true) : null;

        if (!$parsed || !isset($parsed['tier'])) {
            Log::warning("OpenAI classify unparsable for mention #{$mention->id}", ['raw' => $text]);
            return false;
        }

        $tier = $parsed['tier'];
        $modifier = $parsed['modifier'] ?? null;
        if ($modifier && strtolower($modifier) !== 'null') {
            $tier .= ' — ' . $modifier;
        }

        $mention->update([
            'risk_tier' => $tier,
            'risk_reason' => $parsed['reason'] ?? null,
            'content' => $parsed['summary'] ?? $mention->content,
            'classified_at' => now(),
        ]);

        return true;
    }
}