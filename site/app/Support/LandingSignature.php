<?php

namespace App\Support;

class LandingSignature
{
    public static function sign(string $method, string $path, array $query, string $key): string
    {
        return hash_hmac('sha256', static::payload($method, $path, $query), $key);
    }

    public static function payload(string $method, string $path, array $query): string
    {
        $canonical = static::canonicalQuery($query);

        return strtoupper($method).'|'.trim($path, '/').'|'.$canonical;
    }

    public static function canonicalQuery(array $query): string
    {
        unset($query['signature']);
        static::sortRecursive($query);

        return http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    protected static function sortRecursive(array &$value): void
    {
        foreach ($value as &$item) {
            if (is_array($item)) {
                static::sortRecursive($item);
            }
        }

        ksort($value);
    }
}