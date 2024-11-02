<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use App\Models\LentaNews;
use App\Models\RiaNews;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Promise;
class NewsService
{
    public function fetchAllNews(): bool
    {
        if (Cache::has('all_news')) {
            return Cache::get('all_news');
        }

        $client = new Client(['verify' => storage_path('certs/cacert.pem')]);

        $promises = [
            'lenta' => $client->getAsync('https://lenta.ru/rss'),
            'ria' => $client->getAsync('https://ria.ru/export/rss2/archive/index.xml'),
        ];

        $responses = Promise::settle($promises)->wait();

        $lentaSuccess = $this->processNews($responses['lenta']['value'], LentaNews::class, 'lenta.ru');
        $riaSuccess = $this->processNews($responses['ria']['value'], RiaNews::class, 'ria.ru');

        Cache::put('all_news', true, now()->addMinutes(10));

        return $lentaSuccess && $riaSuccess;
    }

    public function fetchLentaLatestNews(): bool
    {
        $client = new Client(['verify' => storage_path('certs/cacert.pem')]);
        $response = $client->get('https://lenta.ru/rss');

        return $this->processNews($response, LentaNews::class, 'lenta.ru');
    }

    public function fetchRiaLatestNews(): bool
    {
        $client = new Client(['verify' => storage_path('certs/cacert.pem')]);
        $response = $client->get('https://ria.ru/export/rss2/archive/index.xml');

        return $this->processNews($response, RiaNews::class, 'ria.ru');
    }

    private function processNews($response, string $model, string $source): bool
    {
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $rssData = simplexml_load_string($response->getBody(), 'SimpleXMLElement', LIBXML_NOCDATA);
        $rssArray = json_decode(json_encode($rssData), true);

        $newsCount = 0;
        if (isset($rssArray['channel']['item']) && is_array($rssArray['channel']['item'])) {
            foreach ($rssArray['channel']['item'] as $item) {
                if ($newsCount >= 10) break;

                // Проверка, является ли описание строкой
                $description = '';
                if (isset($item['description'])) {
                    if (is_array($item['description'])) {
                        $description = implode(' ', $item['description']); // Объединение массива в строку
                    } else {
                        $description = strip_tags($item['description']);
                    }
                } else {
                    $description = $this->fetchDescriptionFromUrl($item['link']);
                }


                $imageUrl = $item['enclosure']['@attributes']['url'] ?? null;

                if (empty($imageUrl)) {
                    // Если изображения нет, попробуем получить его из статьи
                    $imageUrl = $this->fetchImageFromUrl($item['link']);
                }


                $link = $item['link'] ?? null;

                $model::updateOrCreate(
                    ['title' => $item['title']],
                    [
                        'content' => $description ?: 'Описание не найдено',
                        'image' => $imageUrl,
                        'source' => $source,
                        'link' => $link
                    ]
                );

                $newsCount++;
            }
            return true;
        }

        return false;
    }


    private function fetchDescriptionFromUrl(string $url): string
    {
        $pageResponse = Http::withOptions(['verify' => storage_path('certs/cacert.pem')])->get($url);

        if ($pageResponse->getStatusCode() !== 200) {
            return 'Описание не найдено';
        }

        $html = mb_convert_encoding($pageResponse->body(), 'UTF-8', mb_detect_encoding($pageResponse->body(), ['UTF-8', 'ISO-8859-1', 'Windows-1251'], true));
        $crawler = new Crawler($html);
        $description = $crawler->filterXpath('//meta[@name="description"]')->attr('content');

        return $description ?: 'Описание не найдено';
    }
    private function fetchImageFromUrl(string $url): ?string
    {
        $pageResponse = Http::withOptions(['verify' => storage_path('certs/cacert.pem')])->get($url);

        if ($pageResponse->getStatusCode() !== 200) {
            return null;
        }

        $html = mb_convert_encoding($pageResponse->body(), 'UTF-8', mb_detect_encoding($pageResponse->body(), ['UTF-8', 'ISO-8859-1', 'Windows-1251'], true));
        $crawler = new Crawler($html);

        $imageUrl = $crawler->filter('meta[property="og:image"]')->attr('content');

        if (!$imageUrl) {
            $imageElement = $crawler->filter('img')->each(function ($node) {
                $src = $node->attr('src');

                if (!str_contains($src, 'mail.ru/counter')) {
                    return $src;
                }
                return null; // Игнор
            })->first();

            $imageUrl = $imageElement ?: null;
        }

        return $imageUrl;
    }



}
