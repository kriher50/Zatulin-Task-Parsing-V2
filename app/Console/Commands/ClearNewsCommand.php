<?php

namespace App\Console\Commands;

use App\Models\LentaNew;
use App\Models\RiaNew;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClearNewsCommand extends Command
{
    protected $signature = 'news:clear {--force : Принудительное удаление без подтверждения}';
    protected $description = 'Очистить таблицы новостей (с использованием мягкого удаления)';

    public function handle(): void
    {
        if (!$this->option('force') && !$this->confirm('Вы уверены, что хотите удалить все новости?')) {
            $this->info('Операция отменена.');
            return;
        }

        try {
            DB::beginTransaction();

            $lentaCount = LentaNew::count();
            $riaCount = RiaNew::count();

            // Используем мягкое удаление
            LentaNew::query()->delete();
            RiaNew::query()->delete();

            DB::commit();

            $this->info("Новости успешно удалены:");
            $this->table(
                ['Источник', 'Количество удаленных новостей'],
                [
                    ['Lenta.ru', $lentaCount],
                    ['RIA.ru', $riaCount],
                    ['Всего', $lentaCount + $riaCount]
                ]
            );

            Log::info('Новости успешно удалены через команду', [
                'lenta_count' => $lentaCount,
                'ria_count' => $riaCount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при удалении новостей', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->error('Произошла ошибка при удалении новостей: ' . $e->getMessage());
        }
    }
} 