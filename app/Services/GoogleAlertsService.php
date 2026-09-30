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
    $queries = [
        // Stream 1: Nepal English (Local publications)
        [
            'query' => '("Upper Trishuli-1" OR "Upper Trishuli 1" OR "UT-1" OR "Rasuwagadhi" OR "Rasuwa Bhotekoshi" OR "POWERCHINA" OR "SINOHYDRO") AND (Nepal OR Rasuwa OR Trishuli OR flood OR landslide OR tunnel OR protest) -rafting -trekking',
            'params' => '&hl=en-NP&gl=NP&ceid=NP:en'
        ],
        // Stream 2: Devanagari / Nepali Media
        [
            'query' => '("माथिल्लो त्रिशुली" OR "रसुवागढी" OR "सिनोहाइड्रो" OR "पावरचाइना" OR "भोटेकोशी") AND ("रसुवा" OR "त्रिशुली" OR "बाढी" OR "पहिरो" OR "सुरुङ" OR "उद्धार")',
            'params' => '&hl=ne-NP&gl=NP&ceid=NP:ne'
        ],
        // Stream 3: Global Media (Indian & International wires)
        [
            'query' => '("Upper Trishuli-1" OR "Power Construction Corporation of China" OR "Sinohydro Corporation" OR "Sinohydro Bureau 7") AND (Nepal OR Hydropower OR Tunnel OR Dam)',
            'params' => '&hl=en-US&gl=US&ceid=US:en'
        ],
    ];

    $totalCount = 0;

    foreach ($queries as $stream) {
        $url = 'https://news.google.com/rss/search?q=' . urlencode($stream['query']) . $stream['params'];

        try {
            $response = Http::timeout(15)->get($url);
            if (!$response->successful()) continue;

            $xml = @simplexml_load_string($response->body());
            if (!$xml || !isset($xml->channel->item)) continue;

            foreach ($xml->channel->item as $item) {
                $title = strip_tags((string) $item->title);
                $link = (string) $item->link;
                $snippet = strip_tags((string) $item->description);
                $guid = (string) $item->guid;
                $pubDate = (string) $item->pubDate;
                $gnewsId = 'gnews-' . md5($guid);

                // Check exact GUID
                if (AwarioMention::where('awario_id', $gnewsId)->exists()) {
                    continue;
                }

                // Check duplicate by URL/Title
                $existing = $this->dedup->findDuplicate($link, $title);
                if ($existing) {
                    $existing->increment('duplicate_count');
                    continue;
                }

                AwarioMention::create([
                    'awario_id'        => $gnewsId,
                    'alert_id'         => 0,
                    'mentioned_at'     => $pubDate ? \Carbon\Carbon::parse($pubDate) : now(),
                    'title'            => $title,
                    'snippet'          => $snippet,
                    'url'              => $link,
                    'clean_url_hash'   => $this->dedup->hashUrl($link),
                    'clean_title_hash' => $this->dedup->hashTitle($title),
                    'platform'         => 'google-news',
                    'source'           => 'news-blogs',
                    'reach'            => null,
                    'raw'              => json_encode(['guid' => $guid]),
                ]);

                $totalCount++;
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Google News RSS failed: " . $e->getMessage());
        }
    }

    return $totalCount;
}
}