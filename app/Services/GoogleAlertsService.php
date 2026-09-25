<?php

namespace App\Services;

use App\Models\AwarioMention;
use Illuminate\Support\Facades\Http;

class GoogleAlertsService
{
    protected MentionDeduplicationService $dedup;

    public function __construct(MentionDeduplicationService $dedup)
    {
        $this->dedup = $dedup;
    }

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
            $galertId = 'galert-' . md5($guid);

            // 1. Check exact Google Alerts GUID
            if (AwarioMention::where('awario_id', $galertId)->exists()) {
                continue;
            }

            // 2. Cross-source Deduplication Check (Awario, Google News, or Manual)
            $existingDuplicate = $this->dedup->findDuplicate($link, $title);
            if ($existingDuplicate) {
                $existingDuplicate->increment('duplicate_count');
                continue;
            }

            AwarioMention::create([
                'awario_id' => $galertId,
                'alert_id' => 0,
                'mentioned_at' => now(),
                'title' => $title,
                'snippet' => $snippet,
                'url' => $link,
                'clean_url_hash' => $this->dedup->hashUrl($link),
                'clean_title_hash' => $this->dedup->hashTitle($title),
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
    public function syncGoogleNews(): int
    {
        $query = '"Upper Trishuli-1" OR "Rasuwagadhi" OR "POWERCHINA" OR "SINOHYDRO" OR "Rasuwa Bhotekoshi"';
        $url = 'https://news.google.com/rss/search?q=' . urlencode($query) . '&hl=en-US&gl=US&ceid=US:en';

        $response = Http::get($url);
        if (!$response->successful()) {
            return 0;
        }

        $xml = simplexml_load_string($response->body());
        if (!$xml) {
            return 0;
        }

        $count = 0;
        foreach ($xml->channel->item as $item) {
            $title = strip_tags((string) $item->title);
            $link = (string) $item->link;
            $snippet = strip_tags((string) $item->description);
            $guid = (string) $item->guid;
            $pubDate = (string) $item->pubDate;

            $existing = AwarioMention::where('awario_id', 'gnews-' . md5($guid))->first();
            if ($existing) {
                continue;
            }

            AwarioMention::create([
                'awario_id' => 'gnews-' . md5($guid),
                'alert_id' => 0,
                'mentioned_at' => $pubDate ? \Carbon\Carbon::parse($pubDate) : now(),
                'title' => $title,
                'snippet' => $snippet,
                'url' => $link,
                'platform' => 'google-news',
                'source' => 'news-blogs',
                'reach' => null,
                'raw' => json_encode(['guid' => $guid]),
            ]);
            $count = 0;
            foreach ($xml->channel->item as $item) {
                $title = strip_tags((string) $item->title);
                $link = (string) $item->link;
                $snippet = strip_tags((string) $item->description);
                $guid = (string) $item->guid;
                $pubDate = (string) $item->pubDate;
                $gnewsId = 'gnews-' . md5($guid);

                // 1. Check exact Google News GUID
                if (AwarioMention::where('awario_id', $gnewsId)->exists()) {
                    continue;
                }

                // 2. Cross-source Deduplication Check
                $existingDuplicate = $this->dedup->findDuplicate($link, $title);
                if ($existingDuplicate) {
                    $existingDuplicate->increment('duplicate_count');
                    continue;
                }

                AwarioMention::create([
                    'awario_id' => $gnewsId,
                    'alert_id' => 0,
                    'mentioned_at' => $pubDate ? \Carbon\Carbon::parse($pubDate) : now(),
                    'title' => $title,
                    'snippet' => $snippet,
                    'url' => $link,
                    'clean_url_hash' => $this->dedup->hashUrl($link),
                    'clean_title_hash' => $this->dedup->hashTitle($title),
                    'platform' => 'google-news',
                    'source' => 'news-blogs',
                    'reach' => null,
                    'raw' => json_encode(['guid' => $guid]),
                ]);
                $count++;
            }
            $count++;
        }

        return $count;
    }
}
