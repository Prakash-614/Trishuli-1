<?php

namespace App\Console\Commands;

use App\Services\ManualMentionService;
use Illuminate\Console\Command;

class AddManualMention extends Command
{
    protected $signature = 'mention:add-manual';
    protected $description = 'Manually add a mention found outside Awario/Google Alerts (e.g. Facebook/Instagram)';

    public function handle(ManualMentionService $service): int
    {
        $url = $this->ask('Paste the URL');

        if (!$url) {
            $this->error('No URL provided.');
            return self::FAILURE;
        }

        if ($service->exists($url)) {
            $this->warn('This URL is already in the database. Skipping.');
            return self::SUCCESS;
        }

        $this->info('Trying to auto-fetch title/description...');
        $preview = $service->fetchPreview($url);

        if ($preview['title']) {
            $this->line("Found title: {$preview['title']}");
            $title = $this->ask('Title (press Enter to keep the above, or type to override)', $preview['title']);
        } else {
            $this->warn('Could not auto-fetch a title (page blocked or no data). Please type it manually.');
            $title = $this->ask('Title');
        }

        if ($preview['snippet']) {
            $this->line("Found snippet: {$preview['snippet']}");
            $snippet = $this->ask('Snippet (press Enter to keep the above, or type to override)', $preview['snippet']);
        } else {
            $this->warn('Could not auto-fetch a snippet. Please type it manually.');
            $snippet = $this->ask('Snippet / what it says');
        }

        $mention = $service->save($url, $title, $snippet);

        $this->info("Saved as mention #{$mention->id} (source: {$mention->source}).");
        $this->info('Run `php artisan awario:classify` to classify it.');

        return self::SUCCESS;
    }
}