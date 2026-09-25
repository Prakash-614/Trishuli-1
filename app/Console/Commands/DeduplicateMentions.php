<?php

namespace App\Console\Commands;

use App\Models\AwarioMention;
use App\Services\MentionDeduplicationService;
use Illuminate\Console\Command;

class DeduplicateMentions extends Command
{
    protected $signature = 'mentions:deduplicate {--dry-run : Preview duplicates without deleting}';
    protected $description = 'Populate clean URL/title hashes and merge existing duplicates';

    public function handle(MentionDeduplicationService $dedup): int
    {
        $isDryRun = $this->option('dry-run');
        $this->info($isDryRun ? 'Running duplicate check in DRY-RUN mode...' : 'Cleaning existing duplicates...');

        $mentions = AwarioMention::orderBy('id')->get();
        $this->info("Scanning {$mentions->count()} total mentions...");

        $urlMap = [];
        $titleMap = [];
        $deleted = 0;
        $updated = 0;

        foreach ($mentions as $mention) {
            $urlHash = $dedup->hashUrl($mention->url);
            $titleHash = $dedup->hashTitle($mention->title);

            // Populate hashes if missing
            if ($mention->clean_url_hash !== $urlHash || $mention->clean_title_hash !== $titleHash) {
                if (!$isDryRun) {
                    $mention->update([
                        'clean_url_hash' => $urlHash,
                        'clean_title_hash' => $titleHash,
                    ]);
                }
                $updated++;
            }

            // Check URL duplicate
            $duplicateOf = null;
            if ($urlHash && isset($urlMap[$urlHash])) {
                $duplicateOf = $urlMap[$urlHash];
            } elseif ($titleHash && isset($titleMap[$titleHash])) {
                $duplicateOf = $titleMap[$titleHash];
            }

            if ($duplicateOf) {
                $this->warn("Duplicate detected: #{$mention->id} '{$mention->title}' is a duplicate of #{$duplicateOf->id}");

                if (!$isDryRun) {
                    // Transfer reach or classified status to master if master was missing it
                    if (!$duplicateOf->risk_tier && $mention->risk_tier) {
                        $duplicateOf->update([
                            'risk_tier' => $mention->risk_tier,
                            'risk_reason' => $mention->risk_reason,
                            'classified_at' => $mention->classified_at,
                        ]);
                    }
                    if (!$duplicateOf->reach && $mention->reach) {
                        $duplicateOf->update(['reach' => $mention->reach]);
                    }

                    $duplicateOf->increment('duplicate_count');
                    $mention->delete();
                }
                $deleted++;
            } else {
                if ($urlHash) {
                    $urlMap[$urlHash] = $mention;
                }
                if ($titleHash) {
                    $titleMap[$titleHash] = $mention;
                }
            }
        }

        $this->info("Done! Updated {$updated} records. Found and " . ($isDryRun ? "flagged " : "removed ") . "{$deleted} duplicates.");

        return self::SUCCESS;
    }
}