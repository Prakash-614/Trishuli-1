<?php

namespace App\Services;

class UrlNormalizer
{
    public static function normalize(string $url): string
    {
        // Unwrap Google redirect URLs first
        $url = self::extractRealUrl($url);

        $parsed = parse_url($url);
        if (!$parsed || empty($parsed['host'])) {
            return strtolower(trim($url));
        }

        $host = strtolower($parsed['host']);
        $path = rtrim($parsed['path'] ?? '', '/');

        // Strip common tracking params if any query remains
        $query = '';
        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $params);
            $stripKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'fbclid', 'gclid', 'ct', 'cd', 'usg', 'sa', 'rct'];
            foreach ($stripKeys as $key) {
                unset($params[$key]);
            }
            if (!empty($params)) {
                $query = '?' . http_build_query($params);
            }
        }

        return $host . $path . $query;
    }

    protected static function extractRealUrl(string $url): string
    {
        $parsed = parse_url($url);
        if (isset($parsed['host']) && str_contains($parsed['host'], 'google.com') && isset($parsed['query'])) {
            parse_str($parsed['query'], $params);
            if (!empty($params['url'])) {
                return $params['url'];
            }
        }
        return $url;
    }
}