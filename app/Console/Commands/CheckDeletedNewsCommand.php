<?php

namespace App\Console\Commands;

use App\Models\LentaNew;
use App\Models\RiaNew;
use Illuminate\Console\Command;

class CheckDeletedNewsCommand extends Command
{
    protected $signature = 'news:check-deleted';
    protected $description = 'Проверить статус удаленных новостей';

    public function handle(): void
    {
        // Проверяем активные новости
        $activeLentaNews = LentaNew::count();
        $activeRiaNews = RiaNew::count();

        // Проверяем удаленные новости
        $deletedLentaNews = LentaNew::onlyTrashed()->count();
        $deletedRiaNews = RiaNew::onlyTrashed()->count();

        // Проверяем все новости (включая удаленные)
        $allLentaNews = LentaNew::withTrashed()->count();
        $allRiaNews = RiaNew::withTrashed()->count();

        $this->info('Статистика по новостям:');
        $this->table(
            ['Источник', 'Активные', 'Удаленные', 'Всего'],
            [
                ['Lenta.ru', $activeLentaNews, $deletedLentaNews, $allLentaNews],
                ['RIA.ru', $activeRiaNews, $deletedRiaNews, $allRiaNews],
                ['Итого', $activeLentaNews + $activeRiaNews, $deletedLentaNews + $deletedRiaNews, $allLentaNews + $allRiaNews]
            ]
        );

        // Показываем примеры удаленных новостей
        $this->info("\nПримеры последних удаленных новостей Lenta.ru:");
        LentaNew::onlyTrashed()->latest()->limit(3)->get()->each(function ($news) {
            $this->line("- {$news->title} (удалена: {$news->deleted_at})");
        });

        $this->info("\nПримеры последних удаленных новостей RIA.ru:");
        RiaNew::onlyTrashed()->latest()->limit(3)->get()->each(function ($news) {
            $this->line("- {$news->title} (удалена: {$news->deleted_at})");
        });
    }
} 