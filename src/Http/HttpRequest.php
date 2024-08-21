<?php

declare(strict_types=1);

namespace FuturesSpread\Http;

final class HttpRequest
{
    private const int CACHE_TIME = 1800;

    public function make(string $url, array $params): array
    {
        $cachingFile = __DIR__ . '/../../cache/cache.json';

        if (!file_exists($cachingFile)) {
            file_put_contents($cachingFile, json_encode(['time' => 0], JSON_THROW_ON_ERROR));
        }

        $requestUrl = $url . '?' . http_build_query($params);
        $cached = json_decode(file_get_contents($cachingFile), true, 512, JSON_THROW_ON_ERROR);
        $cacheKey = md5($url . ($params['instrument_name'] ?? ''));

        if (isset($cached[$cacheKey]['time']) && time() - $cached[$cacheKey]['time'] < self::CACHE_TIME) {
            error_log('Using cache for ' . $requestUrl);
            return $cached[$cacheKey];
        }

        $response = json_decode(file_get_contents($requestUrl), true, 512, JSON_THROW_ON_ERROR);

        $cached[$cacheKey] = $response;
        $cached[$cacheKey]['time'] = time();

        file_put_contents($cachingFile, json_encode($cached, JSON_THROW_ON_ERROR));

        return $response;
    }
}
