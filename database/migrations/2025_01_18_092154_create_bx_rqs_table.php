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
        Schema::create('bx_rqs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('portal_id')->nullable();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->string('type')->nullable();
            $table->string('bitrix_id')->nullable();
            $table->string('xml_id')->nullable();
            $table->string('entity_type_id')->nullable();
            $table->string('country_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort')->nullable();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bx_rqs');
    }
};
