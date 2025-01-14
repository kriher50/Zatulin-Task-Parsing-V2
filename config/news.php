<?php

return [
    'tables' => [
        'lenta_news',
        'ria_news'
    ],
    
    'cache' => [
        'ttl' => env('NEWS_CACHE_TTL', 10), // minutes
        'key_prefix' => 'news_',
    ],
    
    'sources' => [
        'lenta' => [
            'url' => env('LENTA_NEWS_URL', 'https://lenta.ru/rss'),
            'name' => 'lenta.ru',
            'max_items' => 10,
        ],
        'ria' => [
            'url' => env('RIA_NEWS_URL', 'https://ria.ru/export/rss2/index.xml'),
            'name' => 'ria.ru',
            'max_items' => 20,
        ],
    ],

    'http' => [
        'timeout' => env('NEWS_HTTP_TIMEOUT', 30),
        'connect_timeout' => env('NEWS_HTTP_CONNECT_TIMEOUT', 10),
        'user_agent' => env('NEWS_HTTP_USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
    ],

    'default_image' => env('NEWS_DEFAULT_IMAGE', '/images/no-image.jpg'),
]; 