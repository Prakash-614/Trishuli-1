<?php

namespace App\Services;

use App\Models\AwarioMention;
use Illuminate\Support\Facades\Http;

class ManualMentionService
{
    public function fetchPreview(string $url): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; TamakoshiBot/1.0)',
            ])->timeout(10)->get($url);

            if (!$response->successful()) {
                return ['title' => null, 'snippet' => null, 'title_may_be_just_a_name' => false];
            }

            $html = $response->body();

            preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $titleMatch);
            preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $descMatch);

            $title = isset($titleMatch[1]) ? html_entity_decode($titleMatch[1]) : null;
            $snippet = isset($descMatch[1]) ? html_entity_decode($descMatch[1]) : null;

            // Facebook often puts just the page/profile name in og:title for posts —
            // the real content is usually in og:description instead
            $looksLikeJustAName = $title && str_word_count($title) <= 4 && !str_contains($title, '.') && strlen($title) < 40;

            return [
                'title' => $title,
                'snippet' => $snippet,
                'title_may_be_just_a_name' => $looksLikeJustAName,
            ];
        } catch (\Throwable $e) {
            return ['title' => null, 'snippet' => null, 'title_may_be_just_a_name' => false];
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
        return AwarioMention::where('url', $url)->exists();
    }

    public function save(string $url, string $title, string $snippet): AwarioMention
    {
        return AwarioMention::create([
            'awario_id' => 'manual-' . md5($url . now()),
            'alert_id' => 0,
            'mentioned_at' => now(),
            'title' => $title,
            'snippet' => $snippet,
            'url' => $url,
            'platform' => 'manual',
            'source' => $this->detectSource($url),
            'reach' => null,
            'raw' => json_encode(['entered_manually' => true]),
        ]);
    }
}
