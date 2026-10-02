<?php

namespace App\Services;

use App\Models\AwarioMention;
use Carbon\Carbon;
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
            $response = Http::timeout(12)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'])
                ->get($mention->url);

            if (!$response->successful()) {
                $mention->update(['content_fetched_at' => now()]);
                return;
            }

            $html = $response->body();
            $crawler = new Crawler($html);

            // 1. Try common article containers first, fall back to all <p> tags
            $text = '';
            foreach (['article', 'main', 'body'] as $selector) {
                $node = $crawler->filter($selector);
                if ($node->count() > 0) {
                    $pList = $node->filter('p')->each(fn ($n) => $n->text());
                    $text = implode("\n\n", $pList);
                    if (strlen($text) > 200) break;
                }
            }

            // 2. EXTRACT THE REAL PUBLISH DATE FROM WEBPAGE HTML
            $realDate = $this->extractPublishDate($html, $mention->url);

            $updateData = [
                'raw_content'        => $text ?: null,
                'content_fetched_at' => now(),
            ];

            // If the webpage contains the real original publish date, correct it!
            if ($realDate) {
                $parsedRealDate = Carbon::parse($realDate)->setTimezone('Asia/Kathmandu');
                $updateData['mentioned_at'] = $parsedRealDate;

                // If real date is older than 2 days, update the date so queries filter it correctly without deleting historical records
                if ($parsedRealDate->lt(Carbon::now('Asia/Kathmandu')->subDays(2))) {
                    $mention->update($updateData);
                    return;
                }
            }

            $mention->update($updateData);

        } catch (\Throwable $e) {
            $mention->update(['content_fetched_at' => now()]);
        }
    }

    /**
     * Extracts published date from JSON-LD, OpenGraph, HTML tags, or page text.
     */
    protected function extractPublishDate(string $html, string $url): ?string
    {
        // 1. Check URL path for Full Date (/2026/09/05/...) OR Year/Month (/2026/08/...)
        if (preg_match('/\/(\d{4})\/(\d{1,2})\/(\d{1,2})(?:[\/\-_]|$)/', $url, $m)) {
            return "{$m[1]}-{$m[2]}-{$m[3]}";
        }
        // Catches Artha Sarokar style (/2026/08/slug.html -> defaults to 1st of that month)
        if (preg_match('/\/(\d{4})\/(0[1-9]|1[0-2])\//', $url, $m)) {
            return "{$m[1]}-{$m[2]}-01";
        }

        // 2. Check JSON-LD Schema
        if (preg_match_all('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $ldMatches)) {
            foreach ($ldMatches[1] as $jsonText) {
                if (preg_match('/["\']datePublished["\']\s*:\s*["\']([^"\']+)["\']/i', $jsonText, $dateMatch)) {
                    return $dateMatch[1];
                }
            }
        }

        // 3. Check Meta / OpenGraph tags
        $metaPatterns = [
            '/<meta[^>]+property=["\']article:published_time["\'][^>]+content=["\']([^"\']+)["\']/i',
            '/<meta[^>]+name=["\']publish-date["\'][^>]+content=["\']([^"\']+)["\']/i',
            '/<meta[^>]+name=["\']date["\'][^>]+content=["\']([^"\']+)["\']/i',
        ];
        foreach ($metaPatterns as $pattern) {
            if (preg_match($pattern, $html, $m)) {
                return $m[1];
            }
        }

        // 4. Check HTML text for dates like "Sep 05, 2026" or "September 05, 2026"
        if (preg_match('/(?:[A-Za-z]{3,9}\s+\d{1,2},\s*\d{4})/i', $html, $textMatch)) {
            try {
                return Carbon::parse($textMatch[0])->toDateTimeString();
            } catch (\Throwable $e) {
                // Ignore unparseable text
            }
        }

        return null;
    }
}