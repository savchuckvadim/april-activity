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
        Schema::table('garant_prof_prices', function (Blueprint $table) {
            $table->string('code')->nullable();
            $table->string('complect_code')->nullable();
            $table->string('garant_package_code')->nullable();
            $table->string('supply_type_code')->nullable();
            $table->string('supply_code')->nullable();
            $table->boolean('isSpecial')->default(false); //для акций
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('garant_prof_prices', function (Blueprint $table) {
            $table->dropColumn('code');
            $table->dropColumn('complect_code');
            $table->dropColumn('garant_package_code');
            $table->dropColumn('supply_code');
            $table->dropColumn('isSpecial');
            $table->dropColumn('supply_type_code');

        });
    }
};
