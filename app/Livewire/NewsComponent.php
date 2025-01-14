<?php

namespace App\Livewire;

use App\Models\LentaNew;
use App\Models\RiaNew;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

class NewsComponent extends Component
{
    use WithPagination;
    
    public string $filter = 'all';
    public $newsCount = 0;
    public $perPage = 10;

    protected $listeners = [
        'refresh' => '$refresh',
        'checkNewsStatus' => 'checkNewsStatus',
        'refresh-news' => 'refreshNews',
        'clearJobFinished' => 'handleClearJobFinished'
    ];

    public function refreshNews()
    {
        $this->clearCache();
        $this->updateNewsCount();
        $this->dispatch('$refresh');
    }

    public function handleClearJobFinished()
    {
        $this->clearCache();
        $this->updateNewsCount();
        $this->dispatch('$refresh');
    }

    private function clearCache(): void
    {
        Cache::forget('news_count');
        Cache::forget('total_news_count');
        
        foreach (['all', 'lenta', 'ria'] as $filter) {
            for ($page = 1; $page <= 10; $page++) {
                Cache::forget("filtered_news_{$filter}_{$this->perPage}_{$page}");
            }
        }
    }

    public function mount()
    {
        $this->updateNewsCount();
    }

    private function updateNewsCount(): void
    {
        // Не используем кэш для подсчета, чтобы всегда иметь актуальные данные
        $this->newsCount = LentaNew::whereNotNull('image')->count() + 
                          RiaNew::whereNotNull('image')->count();
    }

    public function checkNewsStatus(): void
    {
        // Получаем актуальное количество без кэша
        $currentCount = LentaNew::count() + RiaNew::count();
        
        if ($currentCount !== $this->newsCount) {
            $this->newsCount = $currentCount;
            $this->dispatch('newsStatusChanged');
        }
    }

    public function getFilteredNews()
    {   
        try {
            // Не используем кэш для получения данных
            switch ($this->filter) {
                case 'lenta':
                    return LentaNew::query()
                        ->whereNotNull('image')
                        ->orderBy('created_at', 'desc')
                        ->paginate($this->perPage);
                case 'ria':
                    return RiaNew::query()
                        ->whereNotNull('image')
                        ->orderBy('created_at', 'desc')
                        ->paginate($this->perPage);
                default:
                    // Оптимизированное получение и объединение новостей
                    $lentaNews = LentaNew::query()
                        ->whereNotNull('image')
                        ->orderBy('created_at', 'desc')
                        ->limit($this->perPage)
                        ->get();
                    
                    $riaNews = RiaNew::query()
                        ->whereNotNull('image')
                        ->orderBy('created_at', 'desc')
                        ->limit($this->perPage)
                        ->get();
                    
                    // Объединяем и сортируем
                    $merged = $lentaNews->concat($riaNews)
                        ->sortByDesc('created_at')
                        ->values();
                        
                    // Создаем пагинатор вручную
                    $page = $this->getPage() ?: 1;
                    $items = $merged->forPage($page, $this->perPage);
                    
                    return new \Illuminate\Pagination\LengthAwarePaginator(
                        $items,
                        $merged->count(),
                        $this->perPage,
                        $page,
                        ['path' => request()->url()]
                    );
            }
        } catch (\Exception $e) {
            Log::error('Error getting filtered news: ' . $e->getMessage(), [
                'exception' => $e,
                'filter' => $this->filter
            ]);
            return collect();
        }
    }

    public function filterNews($filter)
    {
        $this->filter = $filter;
        $this->resetPage();
        $this->clearCache();
    }

    public function render()
    {
        return view('livewire.news-component', [
            'news' => $this->getFilteredNews()
        ]);
    }
}
