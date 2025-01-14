<?php

namespace App\Livewire;

use App\Jobs\ClearNewsTablesJob;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ClearNewsButton extends Component
{
    public string $statusMessage = 'Удалить новости';
    public bool $isLoading = false;
    public string $loadingMessage = '';
    
    private const JOB_STATUS_KEY = 'clear_news_job_status';
    private const CACHE_TIMEOUT_MINUTES = 5;
    private const STATUS_MESSAGES = [
        'running' => 'Удаление новостей...',
        'completed' => 'Новости удалены',
        'failed' => 'Произошла ошибка',
        'pending' => 'Задача в процессе выполнения'
    ];

    public function mount(): void
    {
        $this->dispatch('registerAutoUpdate');
    }

    public function clearNewsButton(): void
    {
        try {
            if ($this->isLoading) {
                return;
            }

            $this->startLoading();
            $jobId = $this->generateJobId();
            
            $this->updateJobStatus($jobId, 'running');
            ClearNewsTablesJob::dispatch($jobId);
            
            $this->dispatch('clearJobStarted', ['jobId' => $jobId]);
            
        } catch (Throwable $e) {
            Log::error('Error dispatching ClearNewsTablesJob', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->handleError($e->getMessage());
        }
    }

    #[Computed]
    public function checkJobStatus(string $jobId): array
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
                return $this->createResponse('pending', self::STATUS_MESSAGES['pending']);
            }
            
            if ($status === 'completed') {
                $this->handleJobCompletion($jobId);
                return $this->createResponse('completed', self::STATUS_MESSAGES['completed']);
            }
            
            if ($status === 'failed') {
                $this->handleJobFailure($jobId);
                return $this->createResponse('failed', self::STATUS_MESSAGES['failed']);
            }
            
            return $this->createResponse('pending', self::STATUS_MESSAGES['pending']);
            
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
            
            $this->clearNewsCache();
            
            $this->resetState(self::STATUS_MESSAGES['completed']);
            $this->dispatch('clearJobFinished');
            
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

    private function clearNewsCache(): void
    {
        Cache::forget('news_count');
        Cache::forget('total_news_count');
        
        foreach (['all', 'lenta', 'ria'] as $filter) {
            for ($page = 1; $page <= 10; $page++) {
                Cache::forget("filtered_news_{$filter}_10_{$page}");
            }
        }
        
        Cache::forget('fetch_status');
        Cache::forget('clear_status');
    }

    private function handleJobFailure(string $jobId): void
    {
        try {
            Cache::forget(self::JOB_STATUS_KEY . $jobId);
            $this->handleError(self::STATUS_MESSAGES['failed']);
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
        $this->resetState(self::STATUS_MESSAGES['failed']);
        $this->dispatch('show-toast', [
            'message' => "Произошла ошибка при удалении новостей: {$message}"
        ]);
    }

    private function startLoading(): void
    {
        $this->isLoading = true;
        $this->loadingMessage = self::STATUS_MESSAGES['running'];
        $this->statusMessage = 'Удаление...';
    }

    private function resetState(string $statusMessage): void
    {
        $this->isLoading = false;
        $this->statusMessage = $statusMessage;
        $this->loadingMessage = '';
    }

    private function generateJobId(): string
    {
        return uniqid('clear_news_', true);
    }

    private function updateJobStatus(string $jobId, string $status): void
    {
        Cache::put(
            self::JOB_STATUS_KEY . $jobId,
            $status,
            now()->addMinutes(self::CACHE_TIMEOUT_MINUTES)
        );
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
        return view('livewire.clear-news-button');
    }
} 
