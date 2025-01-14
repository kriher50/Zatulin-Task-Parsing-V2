<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;

class NewsCacheService
{
    private string $prefix;
    private int $ttl;

    public function __construct(string $prefix, int $ttl)
    {
        $this->prefix = $prefix;
        $this->ttl = $ttl;
    }

    public function remember(string $key, bool $value): void
    {
        Cache::put($this->getKey($key), $value, now()->addMinutes($this->ttl));
    }

    public function get(string $key): ?bool
    {
        return Cache::get($this->getKey($key));
    }

    public function has(string $key): bool
    {
        return Cache::has($this->getKey($key));
    }

    public function forget(string $key): void
    {
        Cache::forget($this->getKey($key));
    }

    private function getKey(string $key): string
    {
        return $this->prefix . $key;
    }
} 