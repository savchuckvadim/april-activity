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
        Schema::table('portal_contracts', function (Blueprint $table) {
            $table->dropForeign(['contract_id']); // Имя ограничения формируется по шаблону: <имя_таблицы>_<имя_колонки>_foreign

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portal_contracts', function (Blueprint $table) {
            //
        });
    }
};
