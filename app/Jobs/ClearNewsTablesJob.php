<?php

namespace App\Jobs;

use App\Contracts\NewsServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\LentaNew;
use App\Models\RiaNew;
use Throwable;

class ClearNewsTablesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 60, 120];
    public $timeout = 180;

    private const JOB_STATUS_KEY = 'clear_news_job_status';
    private const CACHE_TIMEOUT_MINUTES = 5;

    public function __construct(
        private readonly string $jobId
    ) {}

    public function handle(NewsServiceInterface $newsService): void
    {
        try {
            Log::info('Начало очистки таблиц новостей', [
                'job_id' => $this->jobId,
                'tables' => config('news.tables'),
                'time' => now()->toDateTimeString()
            ]);

            $this->updateJobStatus('running');

            // Сначала очищаем таблицы
            $this->clearTables();
            
            // Затем очищаем кэш
            $this->clearCache($newsService);

            $this->updateJobStatus('completed');

            Log::info('Очистка таблиц новостей завершена', [
                'job_id' => $this->jobId,
                'time' => now()->toDateTimeString()
            ]);

            return;
        } catch (Throwable $e) {
            Log::error('Ошибка при очистке таблиц новостей', [
                'job_id' => $this->jobId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->updateJobStatus('failed');
            throw $e;
        }
    }

    private function clearTables(): void
    {
        try {
            DB::beginTransaction();

            // Используем мягкое удаление
            $lentaCount = LentaNew::query()->delete();
            $riaCount = RiaNew::query()->delete();

            DB::commit();

            Log::info('Таблицы успешно очищены через мягкое удаление', [
                'lenta_count' => $lentaCount,
                'ria_count' => $riaCount
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function clearCache(NewsServiceInterface $newsService): void
    {
        try {
            Log::info('Попытка очистки кэша');
            $newsService->clearCache();
            Log::info('Кэш успешно очищен');
        } catch (Throwable $e) {
            Log::error('Ошибка при очистке кэша', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function updateJobStatus(string $status): void
    {
        try {
            Cache::put(
                self::JOB_STATUS_KEY . $this->jobId,
                $status,
                now()->addMinutes(self::CACHE_TIMEOUT_MINUTES)
            );
            
            Log::info('Обновлен статус задачи', [
                'job_id' => $this->jobId,
                'status' => $status
            ]);
        } catch (Throwable $e) {
            Log::error('Ошибка при обновлении статуса задачи', [
                'job_id' => $this->jobId,
                'status' => $status,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Задача очистки новостей завершилась с ошибкой', [
            'job_id' => $this->jobId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        Cache::put(
            self::JOB_STATUS_KEY . $this->jobId,
            'failed',
            now()->addMinutes(self::CACHE_TIMEOUT_MINUTES)
        );
    }
} 