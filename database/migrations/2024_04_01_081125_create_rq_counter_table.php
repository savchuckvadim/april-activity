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
        Schema::create('rq_counter', function (Blueprint $table) {
            $table->unsignedBigInteger('rq_id');
            $table->unsignedBigInteger('counter_id');
            $table->string('type')->nullable();       //invoice offer contract
            $table->integer('value')->nullable();
            $table->string('prefix')->nullable();
            $table->string('postfix')->nullable();
            $table->boolean('day')->default(false);
            $table->boolean('year')->default(false);
            $table->boolean('month')->default(false);
            $table->integer('count')->default(0);
            $table->integer('size')->default(1);
            $table->foreign('rq_id')->references('id')->on('rqs')->onDelete('cascade');
            $table->foreign('counter_id')->references('id')->on('counters')->onDelete('cascade');
            $table->primary(['rq_id', 'counter_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rq_counter');
    }
};
