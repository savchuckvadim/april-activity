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
        Schema::create('offer_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('visibility', ['public', 'private', 'user'])->default('private');
            $table->boolean('is_default')->default(false);
            $table->string('file_path'); // Ссылка на .docx
            $table->string('demo_path')->nullable(); // Ссылка на .pdf
            $table->string('type')->default('single'); // single 3hrd comparison 
            $table->longText('rules')->nullable(); // single 3hrd comparison 
            $table->longText('price_settings')->nullable(); // single 3hrd comparison 
            $table->longText('infoblock_settings')->nullable(); // single 3hrd comparison 
            $table->longText('letter_text')->nullable(); // single 3hrd comparison 
            $table->longText('sale_text_1')->nullable(); // single 3hrd comparison 
            $table->longText('sale_text_2')->nullable(); // single 3hrd comparison 
            $table->longText('sale_text_3')->nullable(); // single 3hrd comparison 
            $table->longText('sale_text_4')->nullable(); // single 3hrd comparison 
            $table->longText('sale_text_5')->nullable(); // single 3hrd comparison 

            $table->longText('field_codes')->nullable(); // single 3hrd comparison 
            $table->string('style')->nullable(); // single 3hrd comparison 
            $table->string('color')->nullable(); // single 3hrd comparison 
            $table->string('code'); // single 3hrd comparison 
            $table->longText('tags')->nullable(); // single 3hrd comparison 
            $table->boolean('is_active')->default(false);
            $table->integer('counter');

            $table->timestamps();
        });

        Schema::create('user_selected_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bitrix_user_id');
            $table->foreignId('portal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('offer_template_id')->constrained('offer_templates')->cascadeOnDelete();
            $table->boolean('is_current')->default(false);
            $table->boolean('is_favorite')->default(false);
            $table->boolean('is_active')->default(false);
            $table->longText('price_settings')->nullable(); // single 3hrd comparison 
            $table->longText('infoblock_settings')->nullable(); // single 3hrd comparison 
            $table->longText('letter_text')->nullable(); // single 3hrd comparison 
            $table->longText('sale_text_1')->nullable(); // single 3hrd comparison 
            $table->longText('sale_text_2')->nullable(); // single 3hrd comparison 
            $table->longText('sale_text_3')->nullable(); // single 3hrd comparison 
            $table->longText('sale_text_4')->nullable(); // single 3hrd comparison 
            $table->longText('sale_text_5')->nullable(); // single 3hrd comparison 

            $table->timestamps();
        });

        Schema::create('offer_template_portal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_template_id')->constrained('offer_templates')->cascadeOnDelete();
            $table->foreignId('portal_id')->constrained('portals')->cascadeOnDelete();
            $table->boolean('is_default')->default(false); // Локальный шаблон по умолчанию для портала
            $table->boolean('is_active')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_template_portal');
        Schema::dropIfExists('user_selected_templates');
        Schema::dropIfExists('offer_templates');
    }
};
