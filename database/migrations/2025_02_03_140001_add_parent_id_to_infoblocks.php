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
        Schema::table('infoblocks', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->constrained('infoblocks');
            $table->foreignId('relation_id')->nullable()->constrained('infoblocks');
            $table->foreignId('related_id')->nullable()->constrained('infoblocks'); // Связанный инфоблок
            $table->foreignId('excluded_id')->nullable()->constrained('infoblocks'); // Исключённый инфоблок
            $table->foreignId('group_id')->nullable()->constrained('info_groups'); // группа

            $table->boolean('isProduct')->nullable(); // Исключённый инфоблок
            $table->boolean('isPackage')->nullable();// Исключённый инфоблок
            $table->string('tag')->nullable()->constrained('infoblocks'); // Исключённый инфоблок

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('infoblocks', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
            $table->dropForeign(['relation_id']);
            $table->dropColumn('relation_id');
            $table->dropForeign(['related_id']);
            $table->dropColumn(['related_id']);
          
            $table->dropForeign(['group_id']);
            $table->dropColumn(['group_id']);
            $table->dropForeign(['excluded_id']);
            $table->dropColumn(['excluded_id']);
            $table->dropColumn(['isProduct']);
            $table->dropColumn(['tag']);


        });
    }
};
