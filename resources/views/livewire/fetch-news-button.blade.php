<div class="flex justify-center mt-4">
    <!-- Кнопка для загрузки новостей -->
    <button wire:click="fetchNews" wire:loading.attr="disabled" class="flex items-center space-x-2 bg-gray-300 dark:bg-gray-600 p-3 rounded-lg transition-all duration-300 ease-in-out hover:bg-gray-400 dark:hover:bg-gray-500 hover:shadow-lg hover:shadow-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-400">
        <span class="text-lg font-semibold text-black dark:text-white" wire:loading.remove>{{ $statusMessage }}</span>
        <span class="text-lg font-semibold text-black dark:text-white" wire:loading>Загрузка...</span>
    </button>
</div>
<script>
    window.addEventListener('reload-page', () => {
        setTimeout(() => {
            location.reload(); // Перезагрузка страницы через 2 секунды
        }, 2000);
    });
</script>
