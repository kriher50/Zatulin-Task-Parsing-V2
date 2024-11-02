<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Artisan;

class ClearNewsTables extends Component
{
    public function clearNewsTables(): void
    {
        Artisan::call('news:clear');
        session()->flash('message', 'Таблицы lenta_news и ria_news успешно очищены!');
        $this->dispatch('reload-page');
    }

    public function render()
    {
        return view('livewire.clear-news-tables');
    }
}
