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
        Schema::create('offer_zakupki_settings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('portal_id')->constrained();
            $table->integer('bxUserId')->nullable();
            $table->integer('offer_template_id')->nullable();
            $table->string('domain')->nullable();
            $table->string('name')->nullable();
            $table->bigInteger('provider1_id')->nullable();
            $table->string('provider1_name')->nullable();
            $table->string('provider1_shortname')->nullable();
            $table->longText('provider1_address')->nullable();
            $table->string('provider1_phone')->nullable();
            $table->string('provider1_email')->nullable();
            $table->longText('provider1_letter_text')->nullable();
            $table->string('provider1_inn')->nullable();
            $table->string('provider1_director')->nullable();
            $table->string('provider1_position')->nullable();
            $table->string('provider1_logo')->nullable();
            $table->string('provider1_stamp')->nullable();
            $table->string('provider1_signature')->nullable();
            $table->float('provider1_price_coefficient')->default(1.05);

            $table->bigInteger('provider2_id')->nullable();
            $table->string('provider2_name')->nullable();
            $table->string('provider2_shortname')->nullable();
            $table->longText('provider2_address')->nullable();
            $table->string('provider2_phone')->nullable();
            $table->string('provider2_email')->nullable();
            $table->longText('provider2_letter_text')->nullable();
            $table->string('provider2_inn')->nullable();
            $table->string('provider2_director')->nullable();
            $table->string('provider2_position')->nullable();
            $table->string('provider2_logo')->nullable();
            $table->string('provider2_stamp')->nullable();
            $table->string('provider2_signature')->nullable();
            $table->float('provider2_price_coefficient')->default(1.1);

            $table->boolean('is_default')->nullable(); //общий для портала
            $table->boolean('is_current')->nullable(); //текущий для юзера
            $table->boolean('is_one_document')->default(true); 
            $table->longText('provider1_price_settings')->nullable();
            $table->longText('provider2_price_settings')->nullable();



        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_zakupki_settings');
    }
};
