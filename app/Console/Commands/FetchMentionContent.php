<?php

namespace App\Console\Commands;

use App\Models\AwarioMention;
use App\Services\ContentFetcherService;
use Illuminate\Console\Command;

class FetchMentionContent extends Command
{
    protected $signature = 'awario:fetch-content';
    protected $description = 'Fetch full page content for mentions that don\'t have it yet';

    public function handle(ContentFetcherService $fetcher): int
    {
        $mentions = AwarioMention::whereNull('content_fetched_at')
            ->where('source', 'news-blogs')
            ->get();

        if ($mentions->isEmpty()) {
            $this->info('No news-blogs mentions need content fetching.');
            return self::SUCCESS;
        }

        $this->info("Fetching content for {$mentions->count()} article-type mentions...");

        foreach ($mentions as $mention) {
            $fetcher->fetch($mention);
            $status = $mention->fresh()->content ? 'OK' : 'empty (page had no extractable text)';
            $this->line("→ #{$mention->id}: {$status}");
        }

        $this->info('Done.');
        return self::SUCCESS;
    }
}
