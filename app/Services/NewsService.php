<?php

namespace App\Services;

use App\Contracts\NewsServiceInterface;
use App\Services\NewsSource\LentaNewsSource;
use App\Services\NewsSource\RiaNewsSource;
use App\Services\Cache\NewsCacheService;
use App\Services\Factories\HttpClientFactory;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class NewsService implements NewsServiceInterface
{
    private Client $client;
    private array $sources;
    private NewsCacheService $cache;

    public function __construct(
        LentaNewsSource $lentaSource,
        RiaNewsSource $riaSource
    ) {
        $this->client = HttpClientFactory::create();
        $this->sources = [
            'lenta' => $lentaSource,
            'ria' => $riaSource
        ];
        $this->cache = new NewsCacheService(
            config('news.cache.key_prefix'),
            (int)config('news.cache.ttl')
        );
    }

    public function fetchLatestNews(): bool
    {
        if ($this->cache->has('fetch_status')) {
            return $this->cache->get('fetch_status');
        }

        $results = [];
        foreach ($this->sources as $name => $source) {
            $results[$name] = $this->fetchWithErrorHandling(
                fn() => $source->fetch($this->client),
                ucfirst($name)
            );
        }
        
        $success = !in_array(false, $results, true);
        
        if ($success) {
            $this->cache->remember('fetch_status', true);
        }

        return $success;
    }

    public function fetchLentaLatestNews(): bool
    {
        return $this->fetchWithErrorHandling(
            fn() => $this->sources['lenta']->fetch($this->client),
            'Lenta'
        );
    }

    public function fetchRiaLatestNews(): bool
    {
        return $this->fetchWithErrorHandling(
            fn() => $this->sources['ria']->fetch($this->client),
            'RIA'
        );
    }

    private function fetchWithErrorHandling(callable $fetchFunction, string $sourceName): bool
    {
        try {
            Log::info("Fetching {$sourceName} news...");
            $result = $fetchFunction();
            Log::info("{$sourceName} fetch result: " . ($result ? 'success' : 'failure'));
            return $result;
        } catch (\Exception $e) {
            Log::error("Error fetching {$sourceName} news", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function clearCache(): void
    {
        $this->cache->forget('fetch_status');
    }
}
