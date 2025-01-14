<?php

namespace App\Jobs;

use App\Contracts\NewsServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Throwable;

class FetchNewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 60, 120]; // Прогрессивные задержки между попытками
    public $timeout = 180; // 3 минуты
    private const JOB_STATUS_KEY = 'fetch_news_job_status';
    private const CACHE_TIMEOUT_MINUTES = 5;

    public function __construct(
        private readonly string $jobId
    ) {}

    public function handle(NewsServiceInterface $newsService): void
    {
        try {
            Log::info('Starting FetchNewsJob', [
                'job_id' => $this->jobId,
                'attempt' => $this->attempts()
            ]);

            $this->updateJobStatus('running');
            
            $result = $newsService->fetchLatestNews();
            
            if (!$result) {
                Log::warning('Failed to fetch news', [
                    'job_id' => $this->jobId,
                    'attempt' => $this->attempts()
                ]);
                
                if ($this->attempts() >= $this->tries) {
                    $this->updateJobStatus('failed');
                    throw new \RuntimeException('Failed to fetch news after ' . $this->tries . ' attempts');
                }
                
                $this->release($this->backoff[$this->attempts() - 1]);
                return;
            }
            
            Log::info('News fetched successfully', [
                'job_id' => $this->jobId,
                'attempt' => $this->attempts()
            ]);
            
            $this->updateJobStatus('completed');
            
        } catch (Throwable $e) {
            Log::error('Error in FetchNewsJob', [
                'job_id' => $this->jobId,
                'attempt' => $this->attempts(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->updateJobStatus('failed');
            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('FetchNewsJob failed', [
            'job_id' => $this->jobId,
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        $this->updateJobStatus('failed');
    }

    private function updateJobStatus(string $status): void
    {
        try {
            Cache::put(
                self::JOB_STATUS_KEY . $this->jobId,
                $status,
                now()->addMinutes(self::CACHE_TIMEOUT_MINUTES)
            );
            
            Log::info('Updated job status', [
                'job_id' => $this->jobId,
                'status' => $status
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to update job status', [
                'job_id' => $this->jobId,
                'status' => $status,
                'message' => $e->getMessage()
            ]);
        }
    }
}
