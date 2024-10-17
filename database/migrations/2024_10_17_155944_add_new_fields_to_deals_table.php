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
        Schema::table('deals', function (Blueprint $table) {
            $table->longText('app_long')->nullable();
            $table->longText('global_long')->nullable();
            $table->longText('currentComplect_long')->nullable();
            $table->longText('od_long')->nullable();
            $table->longText('result_long')->nullable();
            $table->longText('contract_long')->nullable();
            $table->longText('product_long')->nullable();
            $table->longText('rows_long')->nullable();
            $table->longText('regions_long')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            //
        });
    }
};
