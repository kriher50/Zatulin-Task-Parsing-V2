<div class="flex justify-center mt-4">
    <button 
        wire:click="fetchNewsButton" 
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
            let jobCheckInterval;

            @this.on('jobStarted', (event) => {
                const jobId = event.jobId;
                
                jobCheckInterval = setInterval(() => {
                    @this.checkJobStatus(jobId).then(result => {
                        if (result.status === 'completed') {
                            clearInterval(jobCheckInterval);
                            Livewire.dispatch('refresh-news');
                        }
                    });
                }, 2000);
            });

            @this.on('jobFinished', () => {
                if (jobCheckInterval) {
                    clearInterval(jobCheckInterval);
                }
            });
        });
    </script>
</div>
