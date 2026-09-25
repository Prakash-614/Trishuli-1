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

    public function syncMentions(int $alertId): int
    {
        $url = "{$this->base}/alerts/{$alertId}/mentions";
        $params = [
            'access_token' => $this->token,
            'limit' => 300,
        ];

        $count = 0;

        do {
            $response = Http::get($url, $params)->json();

            $mentions = $response['alert_data']['mentions'] ?? [];

            foreach ($mentions as $mention) {
                $url = $mention['url'] ?? null;
                $title = $mention['title'] ?? null;

                // Check if Google Alerts or Google News already captured this article
                $existing = AwarioMention::where('awario_id', $mention['id'])->first();
                if (!$existing && ($url || $title)) {
                    $existing = $this->dedup->findDuplicate($url, $title);
                }

                $data = [
                    'alert_id'          => $alertId,
                    'mentioned_at'      => isset($mention['date'])
                        ? Carbon::createFromTimestampMs($mention['date'])
                        : now(),
                    'reach'             => $mention['reach'] ?? ($existing?->reach ?? null),
                    'language'          => $mention['language'] ?? ($existing?->language ?? null),
                    'url'               => $url ?? ($existing?->url ?? null),
                    'clean_url_hash'    => $this->dedup->hashUrl($url),
                    'clean_title_hash'  => $this->dedup->hashTitle($title),
                    'snippet'           => $mention['snippet'] ?? ($existing?->snippet ?? null),
                    'title'             => $title ?? ($existing?->title ?? null),
                    'sentiment'         => $mention['sentiment'] ?? ($existing?->sentiment ?? null),
                    'source'            => $mention['source'] ?? ($existing?->source ?? null),
                    'author_name'       => $mention['author']['name'] ?? ($existing?->author_name ?? null),
                    'author_url'        => $mention['author']['url'] ?? ($existing?->author_url ?? null),
                    'raw'               => $mention,
                ];

                if ($existing) {
                    // Enrich existing record with Awario's richer reach/author stats without creating a duplicate
                    $existing->update(array_filter($data, fn($v) => $v !== null));
                    $existing->increment('duplicate_count');
                } else {
                    $data['awario_id'] = $mention['id'];
                    $data['platform'] = 'awario';
                    AwarioMention::create($data);
                }

                $count++;
            }

            $next = $response['next'] ?? null;
            $params = $next
                ? ['access_token' => $this->token, 'next' => $next]
                : null;
        } while ($next);

        return $count;
    }
}