<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

/**
 * Модель для новостей с RIA.ru
 * 
 * @property string $title Заголовок новости
 * @property string $content Содержание новости
 * @property string|null $image URL изображения
 * @property string $source Источник новости (ria.ru)
 * @property string $link Ссылка на оригинал
 * @property \Carbon\Carbon $created_at Дата создания
 * @property \Carbon\Carbon $updated_at Дата обновления
 * @property \Carbon\Carbon|null $deleted_at Дата удаления
 */
class RiaNew extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'ria_news';
    
    protected $fillable = [
        'title',
        'content',
        'image',
        'source',
        'link'
    ];

    protected $dates = ['deleted_at'];

    /**
     * Атрибуты, которые должны быть приведены к типам
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Обновляем кэширование без использования тегов
    protected static function booted()
    {
        static::created(function () {
            Cache::forget('news_count');
            Cache::forget('total_news_count');
            Cache::forget('filtered_news_all');
            Cache::forget('filtered_news_ria');
        });
        
        static::updated(function () {
            Cache::forget('news_count');
            Cache::forget('total_news_count');
            Cache::forget('filtered_news_all');
            Cache::forget('filtered_news_ria');
        });

        static::deleted(function () {
            Cache::forget('news_count');
            Cache::forget('total_news_count');
            Cache::forget('filtered_news_all');
            Cache::forget('filtered_news_ria');
        });
    }

    /**
     * Скоуп для получения новостей с изображениями
     */
    public function scopeWithImage(Builder $query): Builder
    {
        return $query->whereNotNull('image');
    }

    /**
     * Скоуп для получения последних новостей
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Аксессор для оптимизации загрузки изображений
     */
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? config('news.default_image')
        );
    }
}
