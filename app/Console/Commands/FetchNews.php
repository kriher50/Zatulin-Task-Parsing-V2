<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NewsService;

class FetchNews extends Command
{
    protected $signature = 'news:fetch';
    protected $description = 'Fetch latest news from lenta.ru and ria.ru';

    protected NewsService $newsService;

    public function __construct(NewsService $newsService)
    {
        parent::__construct();
        $this->newsService = $newsService;
    }

    public function handle()
    {
        $lentaFetched = $this->newsService->fetchLentaLatestNews();
        $riaFetched = $this->newsService->fetchRiaLatestNews();

        if ($lentaFetched) {
            $this->info('Новости с lenta.ru успешно загружены!');
        } else {
            $this->error('Не удалось загрузить новости с lenta.ru.');
        }

        if ($riaFetched) {
            $this->info('Новости с ria.ru успешно загружены!');
        } else {
            $this->error('Не удалось загрузить новости с ria.ru.');
        }
    }
}
