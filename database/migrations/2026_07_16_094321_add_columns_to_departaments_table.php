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
        Schema::table('departaments', function (Blueprint $table) {
           $table->boolean('is_multiple')->default(false); //собирать ли цуп из разрозненных по всей структуре Отделов
           $table->string('multiple_tag')->nullable(); // ОП ОС or custom. По какому тэгу искать эти отделы
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departaments', function (Blueprint $table) {
            $table->dropColumn('is_multiple');
            $table->dropColumn('multiple_tag'); 
        });
    }
};
