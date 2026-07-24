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
        Schema::create('app_cache', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // К какому приложению относится кэш (например: 'kpi', 'sales')
            $table->string('app'); 


            $table->unsignedBigInteger('portal_id');
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('cascade');
            $table->string('domain'); // дубль домена портала для выборок без join


            // ID пользователя Битрикс (0, если кэш общий для всего портала - нужно для корректной работы уникального индекса)
            $table->unsignedBigInteger('bx_user_id')->default(0)->index();

            // Уникальный ключ кэша на портале/приложении
            $table->string('key')->index(); 

            // Группа кэша (например: 'sales', 'service')
            $table->string('group')->nullable()->index(); 

            // Сами данные кэша
            $table->json('data'); 

            // Мета-информация (например, query параметры или контекст запроса)
            $table->json('meta')->nullable();

            // Хэш данных для быстрой сверки изменений
            $table->char('checksum', 32)->nullable();

            // Теги для групповых операций
            $table->json('tags')->nullable(); 

            // Срок действия (null = вечно)
            $table->timestamp('expired_at')->nullable()->index();

            $table->timestamps();
            
            // Составной индекс для супер-быстрой выборки кэша
            $table->unique(['portal_id', 'app', 'key', 'bx_user_id'], 'app_cache_lookup_unique');       
            
            // Обычный индекс (НЕ уникальный) для поиска всех записей конкретного пользователя на портале (например, для сброса его кэша)
            $table->index(['portal_id', 'bx_user_id'], 'app_cache_portal_user_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_cache');
    }
};
