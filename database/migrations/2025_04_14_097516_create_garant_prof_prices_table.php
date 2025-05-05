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
        Schema::create('garant_prof_prices', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('complect_id')->nullable()->nullable();
            $table->foreign('complect_id')->references('id')->on('complects')->onDelete('cascade');
            $table->unsignedBigInteger('garant_package_id')->nullable()->nullable();
            $table->foreign('garant_package_id')->references('id')->on('garant_packages')->onDelete('cascade');
            $table->unsignedBigInteger('supply_id')->nullable();
            $table->foreign('supply_id')->references('id')->on('supplies')->onDelete('cascade');
            $table->string('region_type'); //msk | rgn
            $table->string('supply_type')->nullable(); //internet | proxima для lt null
            $table->float('value'); 
            $table->float('discount')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garant_prof_prices');
    }
};
