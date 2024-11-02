<?php

namespace App\Livewire;

use App\Models\LentaNews;
use App\Models\RiaNews;
use Livewire\Component;

class NewsComponent extends Component
{
    public string $filter = 'all'; // Изменяем значение по умолчанию на 'all'

    public function getFilteredNews()
    {
        // Получаем новости в зависимости от текущего фильтра
        switch ($this->filter) {
            case 'lenta':
                return LentaNews::orderBy('created_at', 'desc')->take(10)->get();
            case 'ria':
                return RiaNews::orderBy('created_at', 'desc')->take(10)->get();
            default:
                // Объединяем все новости из обеих таблиц
                return LentaNews::orderBy('created_at', 'desc')->get()->merge(
                    RiaNews::orderBy('created_at', 'desc')->get()
                );
        }
    }

    public function filterNews($filter)
    {
        $this->filter = $filter; // Устанавливаем выбранный фильтр
    }

    public function render()
    {
        // Получаем отфильтрованные новости
        $news = $this->getFilteredNews();

        return view('livewire.news-component', ['news' => $news]);
    }
}
