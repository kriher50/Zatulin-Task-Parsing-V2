<?php

namespace App\Services\NewsSource;

use App\Models\RiaNew;
use App\Services\NewsSource\Abstract\AbstractNewsSourceService;
use Illuminate\Support\Facades\Log;

class RiaNewsSource extends AbstractNewsSourceService
{
    protected function getSourceConfig(): array
    {
        return config('news.sources.ria');
    }

    protected function createNewsModel(array $parsedData)
    {
        return RiaNew::updateOrCreate(
            ['title' => $parsedData['title']],
            [
                'content' => $parsedData['content'],
                'image' => $parsedData['image'],
                'source' => $this->getSourceConfig()['name'],
                'link' => $parsedData['link'],
                'published_at' => $parsedData['published_at'] ?? now()
            ]
        );
    }

    protected function parseNewsItem(array $item): ?array
    {
        try {
            $title = trim($item['title'] ?? '');
            if (empty($title)) {
                Log::warning('Empty title in RIA news item');
                return null;
            }

            $link = trim($item['link'] ?? '');
            if (empty($link) || !filter_var($link, FILTER_VALIDATE_URL)) {
                Log::warning('Invalid link in RIA news item', ['link' => $link]);
                return null;
            }

            return [
                'title' => $title,
                'content' => $this->processDescription($item),
                'image' => $this->processImage($item),
                'link' => $link,
                'published_at' => isset($item['pubDate']) ? date('Y-m-d H:i:s', strtotime($item['pubDate'])) : null
            ];
        } catch (\Exception $e) {
            Log::error('Error parsing RIA news item: ' . $e->getMessage(), [
                'item' => $item,
                'exception' => $e
            ]);
            return null;
        }
    }

    protected function processImage(array $item): ?string
    {
        // Специфическая логика для RIA, так как у них может быть несколько форматов изображений
        $imageUrl = parent::processImage($item);
        if ($imageUrl) {
            return $imageUrl;
        }

        // Дополнительная проверка для RIA-специфичных форматов
        if (isset($item['description'])) {
            preg_match('/<img[^>]+src="([^">]+)"/', $item['description'], $matches);
            if (!empty($matches[1])) {
                $url = trim($matches[1]);
                return $this->isValidImageUrl($url) ? $url : null;
            }
        }

        return null;
    }

    /**
     * Восстанавливает удаленные новости
     * 
     * @param int $limit Максимальное количество новостей для восстановления
     * @return int Количество восстановленных новостей
     */
    protected function restoreDeletedNews(int $limit): int
    {
        try {
            $deletedNews = RiaNew::onlyTrashed()
                ->latest('deleted_at')
                ->limit($limit)
                ->get();

            $restoredCount = 0;
            foreach ($deletedNews as $news) {
                $news->restore();
                $restoredCount++;
            }

            return $restoredCount;
        } catch (\Exception $e) {
            Log::error("Error restoring deleted news: " . $e->getMessage(), [
                'exception' => $e
            ]);
            return 0;
        }
    }
} 