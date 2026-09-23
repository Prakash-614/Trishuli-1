<?php

namespace App\Services;

use App\Models\AwarioMention;
use Illuminate\Support\Facades\Http;

class GoogleAlertsService
{
    public function syncAll(): int
    {
        $feeds = array_filter(explode(',', config('services.google_alerts.feeds')));
        $total = 0;

        foreach ($feeds as $feedUrl) {
            $total += $this->syncFeed(trim($feedUrl));
        }

        return $total;
    }

    protected function syncFeed(string $feedUrl): int
    {
        $response = Http::get($feedUrl);

        if (!$response->successful()) {
            return 0;
        }

        $xml = simplexml_load_string($response->body());
        if (!$xml) {
            return 0;
        }

        $count = 0;
        foreach ($xml->entry as $entry) {
            $title = strip_tags((string) $entry->title);
            $rawLink = (string) $entry->link['href'];
            $link = $this->extractRealUrl($rawLink);
            $snippet = strip_tags((string) $entry->content);
            $guid = (string) $entry->id;

            $existing = AwarioMention::where('awario_id', 'galert-' . md5($guid))->first();

            if ($existing) {
                continue; // already have this one, skip re-writing it
            }

            AwarioMention::create([
                'awario_id' => 'galert-' . md5($guid),
                'alert_id' => 0,
                'mentioned_at' => now(),
                'title' => $title,
                'snippet' => $snippet,
                'url' => $link,
                'platform' => 'google-alerts',
                'source' => $this->detectSource($link),
                'reach' => null,
                'raw' => json_encode(['feed_url' => $feedUrl, 'guid' => $guid]),
            ]);
            $count++;
        }


        return $count;
    }
    protected function extractRealUrl(string $googleRedirectUrl): string
    {
        $parsed = parse_url($googleRedirectUrl);
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $params);
            if (!empty($params['url'])) {
                return $params['url'];
            }
        }
        return $googleRedirectUrl;
    }

    protected function detectSource(string $url): string
    {
        return match (true) {
            str_contains($url, 'facebook.com') => 'facebook',
            str_contains($url, 'instagram.com') => 'instagram',
            str_contains($url, 'twitter.com'), str_contains($url, 'x.com') => 'twitter',
            str_contains($url, 'youtube.com') => 'youtube',
            default => 'news-blogs',
        };
    }
}