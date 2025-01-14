<div class="flex justify-center mt-4" wire:poll.2000ms="$refresh">
    <button 
        wire:click="clearNewsButton" 
        wire:loading.attr="disabled"
        @class([
            'flex items-center space-x-2 p-3 rounded-lg transition-all duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-400',
            'bg-gray-400 cursor-not-allowed' => $isLoading,
            'bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 hover:shadow-lg hover:shadow-gray-500' => !$isLoading
        ])
        {{ $isLoading ? 'disabled' : '' }}
    >
        <span class="text-lg font-semibold text-black dark:text-white">
            {{ $statusMessage }}
        </span>
        
        @if($isLoading)
            <svg class="animate-spin h-5 w-5 text-black dark:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @endif
    </button>

    @if($isLoading)
        <div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl flex flex-col items-center">
                <svg class="animate-spin h-12 w-12 text-primary dark:text-highlight mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-lg font-semibold text-gray-700 dark:text-gray-300">{{ $loadingMessage }}</span>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            let clearJobCheckInterval = null;
            let currentJobId = null;
            
            function stopStatusCheck() {
                if (clearJobCheckInterval) {
                    clearInterval(clearJobCheckInterval);
                    clearJobCheckInterval = null;
                }
            }

            function handleError(error) {
                console.error('Ошибка при проверке статуса:', error);
                stopStatusCheck();
                Livewire.dispatch('show-toast', { 
                    message: error.message || 'Произошла ошибка при проверке статуса задачи'
                });
            }

            async function checkJobStatus() {
                if (!currentJobId) return;
                
                try {
                    const response = await @this.checkJobStatus(currentJobId);
                    console.log('Job status response:', response);
                    
                    if (response && response.status === 'completed') {
                        stopStatusCheck();
                        Livewire.dispatch('refresh-news');
                    } else if (response && response.status === 'failed') {
                        stopStatusCheck();
                        handleError(new Error(response.message || 'Задача завершилась с ошибкой'));
                    }
                } catch (error) {
                    handleError(error);
                }
            }

            function startStatusCheck(jobId) {
                if (!jobId) {
                    console.error('JobId не определен');
                    return;
                }

                currentJobId = jobId;
                stopStatusCheck();
                clearJobCheckInterval = setInterval(checkJobStatus, 2000);
                // Сразу проверяем статус
                checkJobStatus();
            }

            // Обработчики событий
            Livewire.on('clearJobStarted', (event) => {
                console.log('Job started with event:', event);
                if (event.jobId) {
                    startStatusCheck(event.jobId);
                }
            });

            Livewire.on('clearJobFinished', () => {
                console.log('Job finished');
                stopStatusCheck();
                currentJobId = null;
            });

            // Очистка при размонтировании компонента
            return () => {
                stopStatusCheck();
                currentJobId = null;
            };
        });
    </script>
</div> 