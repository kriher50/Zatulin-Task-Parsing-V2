<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearNewsTables extends Command
{
    protected $signature = 'news:clear';
    protected $description = 'Очистить таблицы lenta_news и ria_news';

    public function handle(): void
    {
        DB::table('lenta_news')->truncate();
        DB::table('ria_news')->truncate();

        $this->info('Таблицы lenta_news и ria_news успешно очищены!');
    }
}
