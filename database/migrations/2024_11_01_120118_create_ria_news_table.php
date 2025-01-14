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
        Schema::create('ria_news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('image')->nullable();
            $table->string('source');
            $table->string('link');
            $table->timestamps();
            $table->softDeletes();

            // Добавляем индексы для оптимизации
            $table->index('created_at');
            $table->index(['image', 'created_at']);
            $table->index('deleted_at');
            $table->fullText(['title', 'content']);
            $table->unique('link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ria_news');
    }
};
