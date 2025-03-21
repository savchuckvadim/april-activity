<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('infoblock_info_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infoblock_id')->constrained()->onDelete('cascade');
            $table->foreignId('info_group_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('infoblock_info_group');
    }
};
