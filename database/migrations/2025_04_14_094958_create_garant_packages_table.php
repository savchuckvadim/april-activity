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
        Schema::create('garant_packages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('infoblock_id')->nullable();
            $table->foreign('infoblock_id')->references('id')->on('infoblocks');
            $table->unsignedBigInteger('info_group_id')->nullable();
            $table->foreign('info_group_id')->references('id')->on('info_groups');

            $table->string('name');
            $table->string('fullName');
            $table->string('shortName');
            $table->longText('description')->nullable();
            $table->string('code');
            $table->string('type');
            $table->string('color')->nullable();
            $table->float('weight')->nullable();
            $table->float('abs')->nullable();

            $table->integer('number');
            $table->string('productType')->nullable(); //lt | consalting | star
            $table->boolean('withABS');
            $table->boolean('isChanging');
            $table->boolean('withDefault');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garant_packages');
    }
};
