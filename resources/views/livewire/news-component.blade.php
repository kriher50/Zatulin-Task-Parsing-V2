<div class="container mx-auto mt-5">
    <h1 class="text-3xl font-bold text-center mb-8 text-primary dark:text-highlight">Новости</h1>

    @if($news->isEmpty())
        <div class="p-5 bg-white border border-gray-200 dark:bg-gray-800 rounded-lg shadow-md text-center">
            <h2 class="text-xl font-semibold text-title dark:text-highlight">Новостей нет 😞</h2>
            <p class="mt-2 text-gray-700 dark:text-gray-400">Для обновления новостей:</p>
            <p class="mt-2 text-gray-700 dark:text-gray-400 font-mono">Нажмите кнопку</p>
            @livewire('fetch-news-button')
            <p class="mt-2 text-gray-700 dark:text-gray-400 font-mono">или в терминале введите:</p>
            <p class="mt-2 text-gray-700 dark:text-gray-400 font-mono">php artisan news:fetch</p>
            <p class="mt-2 text-gray-700 dark:text-gray-400">или запустите:</p>
            <p class="mt-2 text-gray-700 dark:text-gray-400 font-mono">php artisan schedule:work</p>
            <p class="mt-2 text-gray-700 dark:text-gray-400">и ждите 5-ти утра.😊</p>
        </div>
    @else
        <div class="text-center mb-6">
            @livewire('fetch-news-button')
            @livewire('clear-news-tables')
        </div>
        <div class="text-center mb-6 max-w-md mx-auto p-5 bg-white border border-gray-200 dark:bg-gray-800 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold text-title dark:text-highlight mb-4">Фильтр новостей</h2>
            <div class="flex justify-center space-x-4">
                @foreach(['all' => 'Все', 'lenta' => 'Lenta', 'ria' => 'RIA'] as $filter => $label)
                    <button wire:click="filterNews('{{ $filter }}')"
                            class="flex items-center space-x-2 bg-gray-300 dark:bg-gray-600 p-3 rounded-lg transition-all duration-300 ease-in-out hover:bg-gray-400 dark:hover:bg-gray-500 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-gray-400">
                        <span class="text-lg font-semibold text-black dark:text-white">{{ $label }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($news as $newsItem)
                <div class="p-5 bg-white border border-gray-200 dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 flex flex-col">
                    <div class="flex-grow">
                        <h2 class="text-xl font-semibold text-title dark:text-highlight">{{ $newsItem->title }}</h2>
                        <p class="mt-2 text-gray-700 dark:text-gray-400">{{ $newsItem->content }}</p>
                        @if($newsItem->image)
                            <img src="{{ $newsItem->image }}" alt="{{ $newsItem->title }}" class="mt-4 rounded-lg w-full h-auto object-cover transition-transform duration-300 transform hover:scale-105">
                        @endif
                    </div>
                    <div class="mt-4 text-center">
                        <a href="{{ $newsItem->link }}" target="_blank" class="flex items-center justify-center text-center space-x-2 bg-gray-300 dark:bg-gray-600 p-3 rounded-lg transition-all duration-300 ease-in-out hover:bg-gray-400 dark:hover:bg-gray-500 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-gray-400">
                            <span class="text-lg font-semibold text-black dark:text-white">Подробнее</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
