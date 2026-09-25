<?php

namespace App\Services;

use App\Models\AwarioMention;
use Carbon\Carbon;

class MentionDeduplicationService
{
    /**
     * Normalize URL by removing protocol, www, trailing slashes, fragments, and tracking query params.
     */
    public function normalizeUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        // Handle Google News and search redirects
        $url = $this->unwrapRedirectUrl($url);

        $parsed = parse_url(trim($url));
        if (!isset($parsed['host'])) {
            return $url;
        }

        $host = preg_replace('/^www\./i', '', strtolower($parsed['host']));
        $path = isset($parsed['path']) ? rtrim($parsed['path'], '/') : '';

        // Strip tracking query parameters
        $cleanQuery = '';
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $queryParams);
            $trackingParams = [
                'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
                'fbclid', 'gclid', 'ref', 'source', 'oc', 'ved', 'ei', 'usg'
            ];
            foreach ($trackingParams as $param) {
                unset($queryParams[$param]);
            }
            if (!empty($queryParams)) {
                ksort($queryParams);
                $cleanQuery = '?' . http_build_query($queryParams);
            }
        }

        return $host . $path . $cleanQuery;
    }

    /**
     * Compute SHA256 hash of normalized URL for fast indexed matching.
     */
    public function hashUrl(?string $url): ?string
    {
        $normalized = $this->normalizeUrl($url);
        return $normalized ? hash('sha256', $normalized) : null;
    }

    /**
     * Normalize title: strip publisher trailers (e.g. " - The Himalayan Times"), punctuation, whitespace.
     */
    public function normalizeTitle(?string $title): ?string
    {
        if (!$title) {
            return null;
        }

        $clean = html_entity_decode(strip_tags($title), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Remove trailing publisher tags like " - Kathmandu Post" or " | Republica"
        $clean = preg_replace('/(\s*[-–—|:]\s*[^-–—|:]+)$/u', '', $clean);

        // Lowercase and remove punctuation/special characters
        $clean = mb_strtolower($clean, 'UTF-8');
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', '', $clean);
        $clean = preg_replace('/\s+/', ' ', trim($clean));

        return $clean ?: null;
    }

    /**
     * Compute SHA256 hash of normalized title.
     */
    public function hashTitle(?string $title): ?string
    {
        $normalized = $this->normalizeTitle($title);
        return $normalized ? hash('sha256', $normalized) : null;
    }

    /**
     * Find if this mention already exists in the database from any source.
     */
    public function findDuplicate(?string $url, ?string $title, int $windowDays = 14): ?AwarioMention
    {
        $urlHash = $this->hashUrl($url);
        $titleHash = $this->hashTitle($title);

        // 1. Direct match on normalized URL (exact article match)
        if ($urlHash) {
            $matchByUrl = AwarioMention::where('clean_url_hash', $urlHash)->first();
            if ($matchByUrl) {
                return $matchByUrl;
            }
        }

        // 2. Near-duplicate match on Title within recent time window (handles syndicated wires & redirects)
        if ($titleHash && strlen($this->normalizeTitle($title) ?? '') >= 15) {
            $matchByTitle = AwarioMention::where('clean_title_hash', $titleHash)
                ->where('mentioned_at', '>=', Carbon::now()->subDays($windowDays))
                ->first();

            if ($matchByTitle) {
                return $matchByTitle;
            }
        }

        return null;
    }

    /**
     * Unwraps Google redirect URLs (e.g. google.com/url?url=...)
     */
    protected function unwrapRedirectUrl(string $url): string
    {
        if (str_contains($url, 'google.com/url')) {
            $parsed = parse_url($url);
            if (isset($parsed['query'])) {
                parse_str($parsed['query'], $params);
                if (!empty($params['url'])) {
                    return $params['url'];
                }
                if (!empty($params['q'])) {
                    return $params['q'];
                }
            }
        }
        return $url;
    }
}