<?php

namespace App\Services\NewsSource\Abstract;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use App\Services\NewsSource\NewsSourceInterface;

abstract class AbstractNewsSourceService implements NewsSourceInterface
{
    abstract protected function getSourceConfig(): array;
    abstract protected function createNewsModel(array $parsedData);
    abstract protected function parseNewsItem(array $item): ?array;

    public function fetch(Client $client): bool
    {
        try {
            $config = $this->getSourceConfig();
            Log::info("Fetching news from {$config['name']}", ['url' => $config['url']]);
            
            $response = $client->get($config['url']);
            Log::info("Got response from {$config['name']}", ['status' => $response->getStatusCode()]);
            
            return $this->processResponse($response, $config);
        } catch (\Exception $e) {
            Log::error("Error fetching {$config['name']} news: " . $e->getMessage(), [
                'exception' => $e,
                'url' => $config['url']
            ]);
            return false;
        }
    }

    protected function processResponse($response, array $config): bool
    {
        try {
            if ($response->getStatusCode() !== 200) {
                Log::warning("Invalid response status from {$config['name']}", ['status' => $response->getStatusCode()]);
                return false;
            }

            $content = $response->getBody()->getContents();
            if (empty($content)) {
                Log::warning("Empty response from {$config['name']}");
                return false;
            }

            libxml_use_internal_errors(true);
            $rssData = simplexml_load_string($content);
            
            if ($rssData === false) {
                $errors = libxml_get_errors();
                libxml_clear_errors();
                Log::warning("Failed to parse XML from {$config['name']}", ['errors' => $errors]);
                return false;
            }

            $rssArray = json_decode(json_encode($rssData), true);

            if (!isset($rssArray['channel']['item']) || !is_array($rssArray['channel']['item'])) {
                Log::warning("Invalid RSS structure from {$config['name']}");
                return false;
            }

            $newsCount = 0;
            $maxItems = $config['max_items'] ?? 10;

            foreach ($rssArray['channel']['item'] as $item) {
                if ($newsCount >= $maxItems) break;

                try {
                    $parsedData = $this->parseNewsItem($item);
                    if ($parsedData === null) {
                        continue;
                    }

                    $news = $this->createNewsModel($parsedData);
                    
                    if ($news->wasRecentlyCreated || $news->wasChanged()) {
                        $newsCount++;
                        Log::info("Successfully saved {$config['name']} news item", [
                            'title' => $parsedData['title'],
                            'has_image' => !empty($parsedData['image'])
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("Error saving {$config['name']} news item: " . $e->getMessage(), [
                        'item' => $item,
                        'exception' => $e
                    ]);
                    continue;
                }
            }

            if ($newsCount === 0) {
                $restoredCount = $this->restoreDeletedNews($maxItems);
                if ($restoredCount > 0) {
                    Log::info("Restored {$restoredCount} deleted news items from {$config['name']}");
                    return true;
                }
            }

            Log::info("Successfully processed {$config['name']} news", ['count' => $newsCount]);
            return $newsCount > 0;
        } catch (\Exception $e) {
            Log::error("Error processing {$config['name']} response: " . $e->getMessage(), [
                'exception' => $e
            ]);
            return false;
        }
    }

    /**
     * Восстанавливает удаленные новости
     * 
     * @param int $limit Максимальное количество новостей для восстановления
     * @return int Количество восстановленных новостей
     */
    protected function restoreDeletedNews(int $limit): int
    {
        return 0; // По умолчанию не восстанавливаем новости
    }

    protected function processDescription(array $item): string
    {
        if (!isset($item['description'])) {
            return 'Описание не найдено';
        }

        if (is_array($item['description'])) {
            return implode(' ', $item['description']);
        }

        return strip_tags((string)$item['description']);
    }

    protected function processImage(array $item): ?string
    {
        $imageLocations = [
            'enclosure' => ['@attributes', 'url'],
            'media:content' => ['@attributes', 'url'],
            'media:thumbnail' => ['@attributes', 'url']
        ];

        foreach ($imageLocations as $key => $path) {
            $imageUrl = $this->extractNestedValue($item, $key, $path);
            if ($imageUrl && $this->isValidImageUrl($imageUrl)) {
                return $imageUrl;
            }
        }

        return null;
    }

    protected function isValidImageUrl(string $url): bool
    {
        if (strpos($url, '//') === 0) {
            $url = 'https:' . $url;
        } elseif (strpos($url, '/') === 0) {
            $url = 'https://' . parse_url($this->getSourceConfig()['url'], PHP_URL_HOST) . $url;
        }

        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    private function extractNestedValue(array $data, string $key, array $path)
    {
        if (!isset($data[$key])) {
            return null;
        }

        $current = $data[$key];
        foreach ($path as $step) {
            if (!isset($current[$step])) {
                return null;
            }
            $current = $current[$step];
        }

        return trim($current);
    }
} 