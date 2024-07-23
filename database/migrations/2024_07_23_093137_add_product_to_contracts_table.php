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
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('product')->nullable();  // Добавление строки `product`, которая может быть NULL
            $table->longText('description')->nullable();  // Добавление строки `product`, которая может быть NULL

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('product'); // Удаление поля `product`
            $table->dropColumn('description'); // Удаление поля `description`
        });
    }
};
