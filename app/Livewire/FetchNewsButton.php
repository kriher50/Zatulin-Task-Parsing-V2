<?php

namespace App\Livewire;

use App\Jobs\FetchNewsJob;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Throwable;

class FetchNewsButton extends Component
{
    public string $statusMessage = 'Загрузить новости';
    public bool $isLoading = false;
    public string $loadingMessage = '';
    private const JOB_STATUS_KEY = 'fetch_news_job_status';

    public function mount()
    {
        $this->dispatch('registerAutoUpdate');
    }

    public function fetchNewsButton(): void
    {
        try {
            $this->isLoading = true;
            $this->loadingMessage = 'Загрузка новостей...';
            $this->statusMessage = 'Загрузка...';

            // Генерируем уникальный ID для задачи
            $jobId = uniqid('fetch_news_', true);
            
            // Сохраняем статус в кэш
            Cache::put(self::JOB_STATUS_KEY . $jobId, 'running', now()->addMinutes(5));
            
            // Запускаем задачу с ID
            dispatch(new FetchNewsJob($jobId));
            
            // Отправляем ID задачи на фронтенд
            $this->dispatch('jobStarted', jobId: $jobId);
            $this->statusMessage = 'Задача запущена';
            
        } catch (\Exception $e) {
            Log::error('Error dispatching FetchNewsJob: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            
            $this->statusMessage = 'Произошла ошибка';
            $this->loadingMessage = 'Ошибка при загрузке новостей';
            $this->dispatch('show-toast', [
                'message' => 'Произошла ошибка при запуске задачи: ' . $e->getMessage()
            ]);
        }
    }

    public function checkJobStatus($jobId)
    {
        try {
            if (empty($jobId)) {
                return $this->createResponse('error', 'Job ID не указан');
            }

            $status = Cache::get(self::JOB_STATUS_KEY . $jobId);
            
            Log::info('Проверка статуса задачи', [
                'job_id' => $jobId,
                'status' => $status
            ]);
            
            if (!$status) {
                return $this->createResponse('pending', 'Задача в процессе выполнения');
            }
            
            if ($status === 'completed') {
                $this->handleJobCompletion($jobId);
                return $this->createResponse('completed', 'Новости успешно загружены');
            }
            
            if ($status === 'failed') {
                $this->handleJobFailure($jobId);
                return $this->createResponse('failed', 'Произошла ошибка при загрузке новостей');
            }
            
            return $this->createResponse('pending', 'Задача в процессе выполнения');
            
        } catch (Throwable $e) {
            Log::error('Ошибка при проверке статуса задачи', [
                'job_id' => $jobId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->createResponse('error', $e->getMessage());
        }
    }

    private function handleJobCompletion(string $jobId): void
    {
        try {
            Cache::forget(self::JOB_STATUS_KEY . $jobId);
            $this->isLoading = false;
            $this->statusMessage = 'Новости загружены';
            $this->loadingMessage = '';
            $this->dispatch('jobFinished');
            $this->dispatch('refresh-news');
        } catch (Throwable $e) {
            Log::error('Ошибка при завершении задачи', [
                'job_id' => $jobId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->handleError($e->getMessage());
        }
    }

    private function handleJobFailure(string $jobId): void
    {
        try {
            Cache::forget(self::JOB_STATUS_KEY . $jobId);
            $this->handleError('Произошла ошибка при загрузке новостей');
        } catch (Throwable $e) {
            Log::error('Ошибка при обработке неудачного завершения задачи', [
                'job_id' => $jobId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->handleError($e->getMessage());
        }
    }

    private function handleError(string $message): void
    {
        $this->isLoading = false;
        $this->statusMessage = 'Произошла ошибка';
        $this->loadingMessage = '';
        $this->dispatch('show-toast', [
            'message' => $message
        ]);
    }

    private function createResponse(string $status, string $message = ''): array
    {
        return [
            'status' => $status,
            'message' => $message
        ];
    }

    public function render()
    {
        return view('livewire.fetch-news-button');
    }
}
