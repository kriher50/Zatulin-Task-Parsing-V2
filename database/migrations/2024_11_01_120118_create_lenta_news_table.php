<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lenta_news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('image')->nullable();
            $table->string('source')->default('lenta.ru');
            $table->string('link')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Добавляем индексы для оптимизации
            $table->index('created_at');
            $table->index(['image', 'created_at']);
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lenta_news');
    }
};
