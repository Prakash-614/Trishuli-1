<?php

namespace App\Services;

use App\Models\AwarioMention;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AwarioService
{
    protected string $token;
    protected string $base = 'https://api.awario.com/v1.0';
    protected MentionDeduplicationService $dedup;

    public function __construct(MentionDeduplicationService $dedup)
    {
        $this->token = config('services.awario.token');
        $this->dedup = $dedup;
    }

    public function syncMentions(int $alertId, ?\Closure $onProgress = null, int $days = 2): int
    {
       $cutoffTime   = Carbon::now('Asia/Kathmandu')->subDays($days);
        $dateFromMs   = $cutoffTime->getTimestampMs();

        $apiUrl = "{$this->base}/alerts/{$alertId}/mentions";
        $params = [
            'access_token' => $this->token,
            'limit'        => 300,
            'date_from'    => $dateFromMs,
        ];

        $count = 0;
        $page = 1;
        do {
            $httpResponse = Http::timeout(60)->get($apiUrl, $params);

            if (!$httpResponse->successful()) {
                \Illuminate\Support\Facades\Log::error("Awario API failed on page {$page}: " . $httpResponse->body());
                break;
            }

            $response = $httpResponse->json();
            $mentions = $response['alert_data']['mentions'] ?? [];

            if (empty($mentions)) {
                break;
            }

            foreach ($mentions as $mention) {
                // FIXED: Use $mentionUrl instead of $url to prevent overwriting the API endpoint
                $mentionUrl = $mention['url'] ?? null;
                $rawTitle   = $mention['title'] ?? null;
                $snippet    = $mention['snippet'] ?? null;

                // --- SKIP AUTHOR / CATEGORY / TAG ARCHIVE PAGES ---
                if ($mentionUrl && preg_match('/\/(author|tag|category|archive|profile|topics)\/|\/aztecanoticias\/[a-z0-9\-_]+$/i', $mentionUrl)) {
                    continue; // Skips author profiles and category listing pages
                }

                // --- SKIP COMPANY'S OWN OFFICIAL DOMAINS & HOMEPAGES ---
                if ($mentionUrl && preg_match('/(sinohydro\.com|powerchina\.com|powerchina\.cn|nwedc\.com)/i', $mentionUrl)) {
                    continue; // Skips Sinohydro/POWERCHINA's own websites
                }

                // Skip generic root "Homepage" titles (e.g. 首页, Home, Homepage)
                if (in_array(trim($rawTitle ?? ''), ['首页', 'Home', 'Homepage', 'Index'])) {
                    continue;
                }

                // If social media (Instagram, Twitter, FB) has no title, generate one from the caption/author
                $title = !empty(trim($rawTitle ?? ''))
                    ? $rawTitle
                    : (!empty(trim($snippet ?? ''))
                        ? \Illuminate\Support\Str::limit(strip_tags($snippet), 120)
                        : (isset($mention['author']['name']) ? "Post by {$mention['author']['name']}" : 'Untitled Post'));

                // 1. Calculate the exact publication date/time of the mention in Nepal Time
                $mentionDate = isset($mention['date'])
                    ? Carbon::createFromTimestampMs($mention['date'], 'Asia/Kathmandu')
                    : Carbon::now('Asia/Kathmandu');

                // 2. 48-HOUR CUTOFF: Skip anything published before 48 hours ago
                if ($mentionDate->lt($cutoffTime)) {
                    continue;
                }

                // Only check if this exact Awario mention ID already exists (do not merge different posts with matching titles)
                $existing = AwarioMention::where('awario_id', (string) $mention['id'])->first();

                $data = [
                    'alert_id'          => $alertId,
                    'mentioned_at'      => $mentionDate,
                    'reach'             => $mention['reach'] ?? ($existing?->reach ?? null),
                    'language'          => $mention['language'] ?? ($existing?->language ?? null),
                    'url'               => $mentionUrl ?? ($existing?->url ?? null),
                    'clean_url_hash'    => $this->dedup->hashUrl($mentionUrl),
                    'clean_title_hash'  => $this->dedup->hashTitle($title),
                    'snippet'           => $snippet ?? ($existing?->snippet ?? null),
                    'title'             => $title ?? ($existing?->title ?? null),
                    'sentiment'         => $mention['sentiment'] ?? ($existing?->sentiment ?? null),
                    'source'            => $mention['source'] ?? ($existing?->source ?? null),
                    'author_name'       => $mention['author']['name'] ?? ($existing?->author_name ?? null),
                    'author_url'        => $mention['author']['url'] ?? ($existing?->author_url ?? null),
                    'raw'               => $mention,
                ];

                if ($existing) {
                    $existing->update(array_filter($data, fn($v) => $v !== null));
                    $existing->increment('duplicate_count');
                } else {
                    $data['awario_id'] = (string) $mention['id'];
                    $data['platform']  = 'awario';
                    AwarioMention::create($data);
                }

                $count++;
            }

            if ($onProgress) {
                $onProgress($page, count($mentions), $count);
            }

            // Look for next token at alert_data level or root level
            $next = $response['alert_data']['next'] ?? $response['next'] ?? null;

            if ($next) {
                $page++;
                // If next is a full URL, parse or use directly; otherwise keep query params with limit=300
                if (str_starts_with($next, 'http')) {
                    $apiUrl = $next;
                    $params = ['access_token' => $this->token];
                } else {
                    $apiUrl = "{$this->base}/alerts/{$alertId}/mentions";
                    $params = [
                        'access_token' => $this->token,
                        'limit'        => 300,
                        'date_from'    => $dateFromMs,
                        'next'         => $next,
                    ];
                }
            } else {
                $params = null;
            }
        } while ($next && !empty($mentions));

        return $count;
    }
}