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
        Schema::create('bitrix_tokens', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->longText('client_id');
            $table->longText('client_secret');
            $table->longText('access_token');
            $table->longText('refresh_token');
            $table->timestamp('expires_at');
            $table->longText('application_token')->nullable();
            $table->longText('member_id')->nullable();
            $table->unsignedBigInteger('bitrix_app_id');
            $table->foreign('bitrix_app_id')->references('id')->on('bitrix_apps')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitrix_tokens');
    }
};
