<?php

namespace App\Services\NewsSource;

use App\Models\LentaNew;
use App\Services\NewsSource\Abstract\AbstractNewsSourceService;
use Illuminate\Support\Facades\Log;

class LentaNewsSource extends AbstractNewsSourceService
{
    protected function getSourceConfig(): array
    {
        return config('news.sources.lenta');
    }

    protected function createNewsModel(array $parsedData)
    {
        return LentaNew::updateOrCreate(
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
                Log::warning('Empty title in Lenta news item');
                return null;
            }

            $link = trim($item['link'] ?? '');
            if (empty($link) || !filter_var($link, FILTER_VALIDATE_URL)) {
                Log::warning('Invalid link in Lenta news item', ['link' => $link]);
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
            Log::error('Error parsing Lenta news item: ' . $e->getMessage(), [
                'item' => $item,
                'exception' => $e
            ]);
            return null;
        }
    }
} 