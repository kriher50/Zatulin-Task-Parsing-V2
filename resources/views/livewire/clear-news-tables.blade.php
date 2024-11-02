<div class="flex justify-center mt-4">
    <button wire:click="clearNewsTables" wire:loading.attr="disabled" class="flex items-center space-x-2 bg-gray-300 dark:bg-gray-600 p-3 rounded-lg transition-all duration-300 ease-in-out hover:bg-gray-400 dark:hover:bg-gray-500 hover:shadow-lg hover:shadow-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-400">
        <span class="text-lg font-semibold text-black dark:text-white" wire:loading.remove>Удалить все новости?</span>
        <span class="text-lg font-semibold text-black dark:text-white" wire:loading>Загрузка...</span>
    </button>
</div>

@if (session()->has('message'))
    <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
        {{ session('message') }}
    </div>
@endif
