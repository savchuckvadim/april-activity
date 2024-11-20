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
        Schema::create('btx_contacts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');  // имя в битрикс
            $table->string('title');  // Отображаемое имя
            $table->string('code');  // для APP
            $table->unsignedBigInteger('portal_id'); // Добавить после столбца 'code'
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('btx_contacts');
    }
};
