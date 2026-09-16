<?php

namespace App\Services;

use App\Models\AwarioMention;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AwarioService
{
    protected string $token;
    protected string $base = 'https://api.awario.com/v1.0';

    public function __construct()
    {
        $this->token = config('services.awario.token');
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
                AwarioMention::updateOrCreate(
                    ['awario_id' => $mention['id']],
                    [
                        'alert_id'     => $alertId,
                        'mentioned_at' => isset($mention['date'])
                            ? Carbon::createFromTimestampMs($mention['date'])
                            : now(),
                        'reach'        => $mention['reach'] ?? null,
                        'language'     => $mention['language'] ?? null,
                        'url'          => $mention['url'] ?? null,
                        'snippet'      => $mention['snippet'] ?? null,
                        'title'        => $mention['title'] ?? null,
                        'sentiment'    => $mention['sentiment'] ?? null,
                        'source'       => $mention['source'] ?? null,
                        'author_name'  => $mention['author']['name'] ?? null,
                        'author_url'   => $mention['author']['url'] ?? null,
                        'raw'          => $mention,
                    ]
                );
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