<?php

namespace App\Services;

use App\Models\AwarioMention;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class ContentFetcherService
{
    public function fetch(AwarioMention $mention): void
    {
        if (!$mention->url) {
            return;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; TamakoshiBot/1.0)'])
                ->get($mention->url);

            if (!$response->successful()) {
                $mention->update(['content_fetched_at' => now()]);
                return;
            }

            $crawler = new Crawler($response->body());

            // Try common article containers first, fall back to all <p> tags
            $text = '';
            foreach (['article', 'main', 'body'] as $selector) {
                $node = $crawler->filter($selector);
                if ($node->count() > 0) {
                    $text = $node->filter('p')->each(fn ($n) => $n->text());
                    $text = implode("\n\n", $text);
                    if (strlen($text) > 200) break;
                }
            }

            $mention->update([
                'raw_content' => $text ?: null,
                'content_fetched_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $mention->update(['content_fetched_at' => now()]);
        }
    }
}