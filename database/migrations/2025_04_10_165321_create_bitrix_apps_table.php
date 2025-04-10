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
        Schema::create('bitrix_apps', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('portal_id');

            $table->string('group');
            $table->string('type');
            $table->string('code');
            $table->string('status');
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('cascade');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitrix_apps');
    }
};
