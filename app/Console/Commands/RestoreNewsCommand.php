<?php

namespace App\Console\Commands;

use App\Models\LentaNew;
use App\Models\RiaNew;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RestoreNewsCommand extends Command
{
    protected $signature = 'news:restore {--source= : Источник новостей (lenta/ria/all)} {--id= : ID конкретной новости для восстановления}';
    protected $description = 'Восстановить удаленные новости';

    public function handle(): void
    {
        try {
            DB::beginTransaction();

            $source = $this->option('source') ?? 'all';
            $id = $this->option('id');

            if ($id) {
                $this->restoreById($id, $source);
            } else {
                $this->restoreBySource($source);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при восстановлении новостей', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->error('Произошла ошибка при восстановлении новостей: ' . $e->getMessage());
        }
    }

    private function restoreById(int $id, string $source): void
    {
        $restored = false;

        if ($source === 'all' || $source === 'lenta') {
            $news = LentaNew::onlyTrashed()->find($id);
            if ($news) {
                $news->restore();
                $this->info("Восстановлена новость Lenta.ru: {$news->title}");
                $restored = true;
            }
        }

        if ($source === 'all' || $source === 'ria') {
            $news = RiaNew::onlyTrashed()->find($id);
            if ($news) {
                $news->restore();
                $this->info("Восстановлена новость RIA.ru: {$news->title}");
                $restored = true;
            }
        }

        if (!$restored) {
            $this->warn("Новость с ID {$id} не найдена или уже восстановлена");
        }
    }

    private function restoreBySource(string $source): void
    {
        $restoredCount = 0;

        if ($source === 'all' || $source === 'lenta') {
            $count = LentaNew::onlyTrashed()->count();
            LentaNew::onlyTrashed()->restore();
            $restoredCount += $count;
            $this->info("Восстановлено {$count} новостей Lenta.ru");
        }

        if ($source === 'all' || $source === 'ria') {
            $count = RiaNew::onlyTrashed()->count();
            RiaNew::onlyTrashed()->restore();
            $restoredCount += $count;
            $this->info("Восстановлено {$count} новостей RIA.ru");
        }

        if ($restoredCount === 0) {
            $this->info('Нет удаленных новостей для восстановления');
        } else {
            $this->info("Всего восстановлено: {$restoredCount} новостей");
        }

        Log::info('Новости успешно восстановлены', [
            'source' => $source,
            'count' => $restoredCount
        ]);
    }
} 