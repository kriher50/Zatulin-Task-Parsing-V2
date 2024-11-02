<?php

namespace App\Jobs;

use App\Services\NewsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchNewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected NewsService $newsService;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->newsService = new NewsService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Вызов метода для получения новостей
        $this->newsService->fetchLatestNews();
    }
}
