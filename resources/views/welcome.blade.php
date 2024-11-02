<!DOCTYPE html>
<html lang="en" x-data="{
    darkMode: Alpine.store('theme').darkMode,
    showToast: false,
    toastMessage: ''
}" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новости</title>
    @livewireStyles
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100 dark:bg-background dark:text-white">

<header class="bg-white shadow-md dark:bg-gray-900">
    <div class="container mx-auto p-4 flex justify-between items-center">
        <h1 class="text-4xl font-bold text-title dark:text-highlight">Лого-название</h1>
        <button @click="$store.theme.toggleTheme(); darkMode = $store.theme.darkMode" class="flex items-center space-x-2 bg-gray-300 dark:bg-gray-600 p-3 rounded-lg transition-all duration-300 ease-in-out hover:bg-gray-400 dark:hover:bg-gray-500 hover:shadow-lg hover:shadow-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-6 h-6">
                <path d="M15.312 11.424a5.5 5.5 0 0 1-9.201 2.466h2.433a.75.75 0 0 0 0-1.5H3.989a.75.75 0 0 0-.75.75v4.242a.75.75 0 0 0 1.5 0v-2.43a7 7 0 0 0 11.712-3.138.75.75 0 0 0-1.449-.39Zm1.823-2.39a.75.75 0 0 0 0 1.5h2.43a.75.75 0 0 0 0-1.5h-2.43a.75.75 0 0 0 0 1.5h-2.43a.75.75 0 0 0 0 1.5h-2.43a.75.75 0 0 0 0 1.5h-2.43a.75.75 0 0 0 0 1.5h-2.43a.75.75 0 0 0 0 1.5h2.43a.75.75 0 0 0 0-1.5h2.43a.75.75 0 0 0 0-1.5h2.43a.75.75 0 0 0 0-1.5h2.43Zm-5.625-3.89A7.125 7.125 0 0 1 8.725 0c3.473 0 6.287 2.625 6.823 5.975a7.007 7.007 0 0 1 1.365.205A7.125 7.125 0 0 1 15 14a7.125 7.125 0 0 1-3.225.87 7.087 7.087 0 0 1-5.65-2.693A7.125 7.125 0 0 1 6.597 6.012c-.4-.144-.8-.338-1.182-.586a.75.75 0 1 0-.876 1.22c.325.227.692.413 1.078.563C7.66 7.863 8.34 9.029 8.34 10.525c0 1.25-.25 2.363-.63 3.378A5.5 5.5 0 0 1 8.706 5.86 5.5 5.5 0 0 1 15 12.5c0 1.535-.68 2.954-1.765 3.85a.75.75 0 1 0 .746 1.286A7.117 7.117 0 0 0 15 12.5c0-3.88-3.138-7-7-7Z"/>
            </svg>
            <span class="text-lg font-semibold dark:text-white">Смена темы</span>
        </button>
    </div>
</header>


<main class="container mx-auto mt-5">
    @livewire('news-component')
</main>

@livewireScripts
<script src="https://cdn.jsdelivr.net/npm/alpinejs" defer></script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('theme', {
            darkMode: localStorage.getItem('darkMode') === 'true', // Загружаем состояние из localStorage
            toggleTheme() {
                this.darkMode = !this.darkMode;
                localStorage.setItem('darkMode', this.darkMode); // Сохраняем состояние в localStorage
            }
        });
    });
</script>
</body>
</html>
