<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Маркетплейс Битрикс24: продукты внутри приложения (entitlement'ы).
     *
     * Одно маркетплейс-приложение содержит продукты (sales — сразу,
     * service — позже). Оплата — по прямым договорам вне Битрикса;
     * активацию/деактивацию продукта выполняет вендор из админки.
     * Одна строка = доступ портала к продукту.
     */
    public function up(): void
    {
        Schema::create('portal_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();

            $table->unsignedBigInteger('portal_id');
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('cascade');

            $table->string('product_code'); // sales | service
            $table->string('edition')->nullable(); // исполнение продукта (на будущее: full | calls | konstructor ...)
            $table->string('status')->default('inactive'); // inactive | trial | active | expired
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('paid_until')->nullable(); // срок по договору (опционально)

            $table->unique(['portal_id', 'product_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_products');
    }
};
