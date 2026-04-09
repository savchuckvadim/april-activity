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
        Schema::create('invoice_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->enum('visibility', ['public', 'portal', 'provider'])->default('portal');

            $table->longText('file_path'); // Ссылка на .docx
            $table->longText('demo_path')->nullable(); // Ссылка на .pdf
            $table->enum('type', ['word', 'excel', 'pdf', 'html', 'other'])->default('word');
            $table->longText('name'); // single 3hrd comparison 
            $table->string('code'); // single 3hrd comparison 
            $table->integer('counter');
            $table->longText('description')->nullable(); // single 3hrd comparison 
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->boolean('is_archived')->default(false); // вместо удаления переносим в архив
            $table->date('archived_at')->nullable(); // дата архивации - через год все архивы удаляем
            $table->unsignedBigInteger('portal_id')->nullable();
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('set null');
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('set null');
            $table->unsignedBigInteger('creator_bitrix_user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_templates');

    }
};
