<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Illuminate\Support\Facades\Artisan;

class FetchNewsButton extends Component
{
    public string $statusMessage = 'Загрузить новости'; // Изначальное сообщение

    public function fetchNews(): void
    {
        try {
        Artisan::call('news:fetch');
        $output = Artisan::output();

        //   Log::info('Вывод команды: ' . $output);

        if (str_contains($output, 'успешно загружены')) {
            $this->statusMessage = 'Успех!!!';
            $this->dispatch('reload-page');
        } else {
            $this->statusMessage = 'Ошибка при загрузке';
            // Log::error('Ошибка при загрузке новостей: ' . $output);
            $this->dispatch('show-toast', ['message' => 'Ошибка при загрузке новостей.']);
        }
    } catch (\Exception $e) {
        $this->statusMessage = 'Произошла ошибка';
        //  Log::error('Произошла ошибка: ' . $e->getMessage(), ['exception' => $e]);
        $this->dispatch('show-toast', ['message' => 'Произошла ошибка: ' . $e->getMessage()]);
    }
    }

    public function render()
    {
        return view('livewire.fetch-news-button');
    }
}
