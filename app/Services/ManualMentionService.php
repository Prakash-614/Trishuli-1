<?php

namespace App\Services;

use App\Models\AwarioMention;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class ManualMentionService
{
    protected MentionDeduplicationService $dedup;

    public function __construct(MentionDeduplicationService $dedup)
    {
        $this->dedup = $dedup;
    }

    public function fetchPreview(string $url): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ])->timeout(12)->get($url);

            if (!$response->successful()) {
                return [
                    'title' => null,
                    'snippet' => null,
                    'published_at' => null,
                    'title_may_be_just_a_name' => false,
                ];
            }

            $html = $response->body();

            // 1. Extract OpenGraph Title & Description
            preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $titleMatch);
            preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $descMatch);

            $title = isset($titleMatch[1]) ? html_entity_decode(trim($titleMatch[1])) : null;
            $snippet = isset($descMatch[1]) ? html_entity_decode(trim($descMatch[1])) : null;

            // Fallback for Title if og:title was not found
            if (!$title && preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $fallbackTitle)) {
                $title = html_entity_decode(trim(strip_tags($fallbackTitle[1])));
            }

            // 2. Extract Publication Date
            $publishedAt = $this->extractPublishDate($html, $url);

            // Facebook title check
            $looksLikeJustAName = $title && str_word_count($title) <= 4 && !str_contains($title, '.') && strlen($title) < 40;

            return [
                'title' => $title,
                'snippet' => $snippet,
                'published_at' => $publishedAt,
                'title_may_be_just_a_name' => $looksLikeJustAName,
            ];
        } catch (\Throwable $e) {
            return [
                'title' => null,
                'snippet' => null,
                'published_at' => null,
                'title_may_be_just_a_name' => false,
            ];
        }
    }

    /**
     * Extracts published date from JSON-LD, OpenGraph, Schema.org, or HTML5 <time> tags.
     */
    /**
     * Extracts published date from JSON-LD, OpenGraph, HTML tags, or the URL itself.
     */
    protected function extractPublishDate(string $html, string $url): ?string
    {
        // 1. Check URL path for date (e.g. kathmandupost.com/.../2024/09/15/...)
        if (preg_match('/\/(\d{4})\/(\d{1,2})\/(\d{1,2})(?:[\/\-_]|$)/', $url, $urlMatch)) {
            $parsed = $this->tryParseDate("{$urlMatch[1]}-{$urlMatch[2]}-{$urlMatch[3]}");
            if ($parsed) return $parsed;
        }

        // 2. Check JSON-LD Schema (Ratopati, JoongAng Daily, WordPress, Reuters, etc.)
        if (preg_match_all('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $ldMatches)) {
            foreach ($ldMatches[1] as $jsonText) {
                if (preg_match('/["\']datePublished["\']\s*:\s*["\']([^"\']+)["\']/i', $jsonText, $dateMatch)) {
                    $parsed = $this->tryParseDate($dateMatch[1]);
                    if ($parsed) return $parsed;
                }
                if (preg_match('/["\']dateCreated["\']\s*:\s*["\']([^"\']+)["\']/i', $jsonText, $dateMatch)) {
                    $parsed = $this->tryParseDate($dateMatch[1]);
                    if ($parsed) return $parsed;
                }
            }
        }

        // 3. Check Meta / OpenGraph tags (Kathmandu Post, Republica, international outlets)
        $metaPatterns = [
            '/<meta[^>]+property=["\']article:published_time["\'][^>]+content=["\']([^"\']+)["\']/i',
            '/<meta[^>]+name=["\']article:published_time["\'][^>]+content=["\']([^"\']+)["\']/i',
            '/<meta[^>]+itemprop=["\']datePublished["\'][^>]+content=["\']([^"\']+)["\']/i',
            '/<meta[^>]+name=["\']publish-date["\'][^>]+content=["\']([^"\']+)["\']/i',
            '/<meta[^>]+name=["\']pubdate["\'][^>]+content=["\']([^"\']+)["\']/i',
            '/<meta[^>]+name=["\']publishdate["\'][^>]+content=["\']([^"\']+)["\']/i',
            '/<meta[^>]+name=["\']date["\'][^>]+content=["\']([^"\']+)["\']/i',
            '/<meta[^>]+name=["\']parsely-pub-date["\'][^>]+content=["\']([^"\']+)["\']/i',
            '/<meta[^>]+name=["\']sailthru.date["\'][^>]+content=["\']([^"\']+)["\']/i',
        ];

        foreach ($metaPatterns as $pattern) {
            if (preg_match($pattern, $html, $m)) {
                $parsed = $this->tryParseDate($m[1]);
                if ($parsed) return $parsed;
            }
        }

        // 4. Check HTML5 <time datetime="..."> or Kathmandu Post date containers
        if (preg_match('/<time[^>]+datetime=["\']([^"\']+)["\']/i', $html, $timeMatch)) {
            $parsed = $this->tryParseDate($timeMatch[1]);
            if ($parsed) return $parsed;
        }

        // 5. Check text inside date classes (e.g. Published at : September 15, 2024)
        if (preg_match('/(?:Published(?:\s+at)?\s*:?\s*)([A-Za-z]+\s+\d{1,2},\s*\d{4})/i', $html, $textMatch)) {
            $parsed = $this->tryParseDate($textMatch[1]);
            if ($parsed) return $parsed;
        }

        return null;
    }

    /**
     * Safely parse raw date string into Carbon datetime string.
     */
    protected function tryParseDate(?string $dateStr): ?string
    {
        if (!$dateStr) return null;
        try {
            return Carbon::parse(trim($dateStr))->toDateTimeString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function detectSource(string $url): string
    {
        return match (true) {
            str_contains($url, 'facebook.com') => 'facebook',
            str_contains($url, 'instagram.com') => 'instagram',
            str_contains($url, 'twitter.com'), str_contains($url, 'x.com') => 'twitter',
            str_contains($url, 'youtube.com') => 'youtube',
            default => 'news-blogs',
        };
    }

    public function exists(string $url): bool
    {
        $hash = $this->dedup->hashUrl($url);
        return AwarioMention::where('clean_url_hash', $hash)
            ->orWhere('url', $url)
            ->exists();
    }

    public function save(string $url, string $title, string $snippet, ?string $publishedAt = null): AwarioMention
    {
        $mentionedAt = $publishedAt ? Carbon::parse($publishedAt) : now();

        return AwarioMention::create([
            'awario_id' => 'manual-' . md5($url . microtime()),
            'alert_id' => 0,
            'mentioned_at' => $mentionedAt,
            'title' => $title,
            'snippet' => $snippet,
            'url' => $url,
            'clean_url_hash' => $this->dedup->hashUrl($url),
            'clean_title_hash' => $this->dedup->hashTitle($title),
            'platform' => 'manual',
            'source' => $this->detectSource($url),
            'reach' => null,
            'raw' => [
                'entered_manually' => true,
                'extracted_publish_date' => $publishedAt,
            ],
        ]);
    }
}