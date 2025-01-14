{{-- Главная страница новостей --}}
<div class="container mx-auto mt-5">
    <h1 class="text-3xl font-bold text-center mb-8 text-primary dark:text-highlight">
        Новости
    </h1>

    @if($news->isEmpty())
        {{-- Блок с инструкциями по обновлению новостей --}}
        <div class="p-8 bg-white border-2 border-primary/20 dark:border-gray-200 
                    dark:bg-gray-800 rounded-xl shadow-lg text-center max-w-2xl mx-auto">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-primary dark:text-highlight mb-4">
                    Новостей нет 😞
                </h2>
                <p class="text-lg text-primary/80 dark:text-gray-400">
                    Для обновления новостей выберите один из способов:
                </p>
            </div>

            <div class="space-y-8">
                {{-- Способ 1: Кнопка --}}
                <div class="bg-primary/5 dark:bg-gray-700 rounded-lg p-4 
                            border border-primary/10 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-primary/70 dark:text-gray-300 mr-2" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122">
                            </path>
                        </svg>
                        <span class="text-primary/90 dark:text-gray-300 font-semibold">
                            Вариант 1: Нажмите кнопку
                        </span>
                    </div>
                    @livewire('fetch-news-button')
                </div>

                {{-- Способ 2: Команда --}}
                <div class="bg-primary/5 dark:bg-gray-700 rounded-lg p-4 
                            border border-primary/10 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-primary/70 dark:text-gray-300 mr-2" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        <span class="text-primary/90 dark:text-gray-300 font-semibold">
                            Вариант 2: Выполните команду
                        </span>
                    </div>
                    <div class="bg-white dark:bg-gray-600 rounded-lg p-3 
                                border border-primary/10">
                        <code class="text-sm font-mono text-primary/90 dark:text-gray-200">
                            php artisan news:fetch
                        </code>
                    </div>
                </div>

                {{-- Способ 3: Планировщик --}}
                <div class="bg-primary/5 dark:bg-gray-700 rounded-lg p-4 
                            border border-primary/10 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-primary/70 dark:text-gray-300 mr-2" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                        <span class="text-primary/90 dark:text-gray-300 font-semibold">
                            Вариант 3: Автоматическое обновление
                        </span>
                    </div>
                    <div class="bg-white dark:bg-gray-600 rounded-lg p-3 mb-2 
                                border border-primary/10">
                        <code class="text-sm font-mono text-primary/90 dark:text-gray-200">
                            php artisan schedule:work
                        </code>
                    </div>
                    <p class="text-sm text-primary/70 dark:text-gray-400">
                        Обновление произойдет автоматически в 5:00 утра
                    </p>
                </div>

                {{-- Важное примечание для работы очереди --}}
                <div class="bg-amber-50 dark:bg-yellow-900/20 rounded-lg p-4 
                            border-l-4 border-amber-400 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-amber-600 dark:text-yellow-400 mr-2" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                        <span class="text-amber-900 dark:text-yellow-400 font-semibold">
                            Важно: Запустите обработчик очереди
                        </span>
                    </div>
                    <div class="bg-amber-100/50 dark:bg-yellow-900/40 rounded-lg p-3 mb-2 
                                border border-amber-200">
                        <code class="text-sm font-mono text-amber-900 dark:text-yellow-300">
                            php artisan queue:work --daemon
                        </code>
                    </div>
                    <p class="text-sm text-amber-800 dark:text-yellow-400">
                        Необходимо для обработки задач загрузки и удаления новостей
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="text-center mb-6">
            @livewire('fetch-news-button')
            @livewire('clear-news-button')
        </div>

        <div class="text-center mb-6 max-w-md mx-auto p-5 bg-white 
                    border border-primary/20 dark:border-gray-200 dark:bg-gray-800 
                    rounded-lg shadow-md">
            <h2 class="text-xl font-semibold text-primary dark:text-highlight mb-4">
                Фильтр новостей
            </h2>
            <div class="flex justify-center space-x-4">
                @foreach(['all' => 'Все', 'lenta' => 'Lenta', 'ria' => 'RIA'] as $filter => $label)
                    <button wire:click="filterNews('{{ $filter }}')"
                            class="flex items-center space-x-2 bg-primary/10 dark:bg-gray-600 p-3 
                                   rounded-lg transition-all duration-300 ease-in-out 
                                   hover:bg-primary/20 dark:hover:bg-gray-500 hover:shadow-lg 
                                   focus:outline-none focus:ring-2 focus:ring-primary/30
                                   {{ $filter === $this->filter ? 'ring-2 ring-primary/50' : '' }}">
                        <span class="text-lg font-semibold text-primary dark:text-white">
                            {{ $label }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($news as $newsItem)
                <div class="p-5 bg-white border border-primary/20 dark:border-gray-200 
                            dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg 
                            transition-all duration-300 flex flex-col">
                    <div class="flex-grow">
                        <h2 class="text-xl font-semibold text-primary dark:text-highlight mb-4">
                            {{ $newsItem->title }}
                        </h2>
                        
                        @if($newsItem->image)
                            <div class="relative pt-[56.25%] mb-4">
                                <img src="{{ $newsItem->image }}" 
                                     alt="{{ $newsItem->title }}" 
                                     class="absolute inset-0 w-full h-full object-cover rounded-lg
                                            transition-transform duration-300 transform hover:scale-105">
                            </div>
                        @endif
                        
                        <p class="text-gray-600 dark:text-gray-300 mb-4">
                            {{ Str::limit(strip_tags($newsItem->content), 150) }}
                        </p>
                    </div>
                    
                    <div class="mt-4 flex justify-between items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $newsItem->source }}
                        </span>
                        <a href="{{ $newsItem->link }}" 
                           target="_blank" 
                           class="inline-flex items-center space-x-2 bg-primary/10 dark:bg-gray-600 
                                  px-4 py-2 rounded-lg transition-all duration-300 ease-in-out 
                                  hover:bg-primary/20 dark:hover:bg-gray-500 hover:shadow-lg 
                                  focus:outline-none focus:ring-2 focus:ring-primary/30">
                            <span class="text-primary dark:text-white font-medium">
                                Подробнее
                            </span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($news instanceof \Illuminate\Pagination\LengthAwarePaginator && $news->hasPages())
            <div class="mt-6">
                {{ $news->links() }}
            </div>
        @endif
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            let statusCheckInterval;

            function startStatusCheck() {
                statusCheckInterval = setInterval(() => {
                    @this.checkNewsStatus().then(result => {
                        if (result.status === 'changed') {
                            Livewire.dispatch('refresh-news');
                        }
                    });
                }, 2000);
            }

            startStatusCheck();

            document.addEventListener('beforeunload', () => {
                if (statusCheckInterval) {
                    clearInterval(statusCheckInterval);
                }
            });

            @this.on('newsStatusChanged', () => {
                Livewire.dispatch('refresh-news');
            });
        });
    </script>
</div>
