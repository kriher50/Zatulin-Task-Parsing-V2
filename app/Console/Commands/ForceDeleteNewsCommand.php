<?php

namespace App\Console\Commands;

use App\Models\LentaNew;
use App\Models\RiaNew;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ForceDeleteNewsCommand extends Command
{
    protected $signature = 'news:force-delete {--force : Принудительное удаление без подтверждения}';
    protected $description = 'Полностью удалить все новости из базы данных (ОПАСНАЯ ОПЕРАЦИЯ!)';

    public function handle(): void
    {
        if (!$this->option('force')) {
            $this->error('ВНИМАНИЕ! Эта операция полностью удалит все новости без возможности восстановления!');
            if (!$this->confirm('Вы абсолютно уверены, что хотите продолжить?')) {
                $this->info('Операция отменена.');
                return;
            }
            
            if (!$this->confirm('Пожалуйста, подтвердите еще раз для полного удаления данных')) {
                $this->info('Операция отменена.');
                return;
            }
        }

        try {
            DB::beginTransaction();

            // Получаем количество новостей перед удалением
            $lentaCount = LentaNew::withTrashed()->count();
            $riaCount = RiaNew::withTrashed()->count();

            // Полностью удаляем все новости, включая мягко удаленные
            LentaNew::withTrashed()->forceDelete();
            RiaNew::withTrashed()->forceDelete();

            DB::commit();

            $this->info("Новости полностью удалены из базы данных:");
            $this->table(
                ['Источник', 'Количество удаленных новостей'],
                [
                    ['Lenta.ru', $lentaCount],
                    ['RIA.ru', $riaCount],
                    ['Всего', $lentaCount + $riaCount]
                ]
            );

            Log::info('Новости полностью удалены из базы данных', [
                'lenta_count' => $lentaCount,
                'ria_count' => $riaCount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при полном удалении новостей', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->error('Произошла ошибка при удалении новостей: ' . $e->getMessage());
        }
    }
} 