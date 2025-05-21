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
        Schema::create('transcriptions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('provider')->nullable();
            $table->string('activity_id')->nullable();
            $table->string('file_id')->nullable();
            $table->boolean('in_comment')->default(false);
            $table->string('status')->nullable(); //in_progress | fail | success
            $table->longText('text')->nullable();
            $table->string('symbols_count')->nullable();
            $table->string('price')->nullable();
            $table->string('duration')->nullable();
            $table->string('domain')->nullable();
            $table->string('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('entity_type')->nullable();
            $table->string('entity_id')->nullable();
            $table->string('entity_name')->nullable();
            $table->string('app')->nullable();
            $table->string('department')->nullable(); // sales | service | tmc
            // $table->string('portal_id')->nullable();
            $table->longText('user_comment')->nullable();
            $table->longText('owner_comment')->nullable();
            $table->text('user_mark')->nullable();
            $table->text('owner_mark')->nullable();
            $table->json('user_result')->nullable();
            $table->longText('report_result')->nullable();
            $table->boolean('in_report')->default(false);
            $table->string('report_item_id')->nullable();
            $table->string('portal_id')->foreignId('portals')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcriptions');
    }
};
