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
        Schema::create('bx_document_deals', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('portalId')->nullable();
            $table->integer('dealId')->nullable();  //id сделки в битриксе
            $table->integer('userId')->nullable();
            $table->foreignId('infoblockId')->nullable();
            $table->foreignId('supplyId')->nullable();
            $table->foreignId('contractId')->nullable();
            $table->foreignId('complectId')->nullable();
            $table->foreignId('actionId')->nullable();
            $table->foreignId('regionId')->nullable();
            $table->foreignId('favoriteId')->nullable();
            $table->foreignId('templateId')->nullable();
            $table->string('title')->nullable();
            $table->string('domain')->nullable();
            $table->string('dealName')->nullable();
            $table->longText('app')->nullable();
            $table->longText('global')->nullable();
            $table->longText('currentComplect')->nullable();
            $table->longText('od')->nullable();
            $table->longText('result')->nullable();
            $table->longText('contract')->nullable();
            $table->longText('product')->nullable();
            $table->longText('rows')->nullable();
            $table->longText('regions')->nullable();
            $table->longText('tags')->nullable();
            $table->string('department')->nullable();
            $table->string('target')->nullable();
            $table->string('promotionName')->nullable();
            $table->longText('promotion')->nullable();
            $table->string('code')->nullable();
            $table->integer('order')->nullable();  //id сделки в битриксе
            $table->string('group')->nullable();
            $table->string('clientGroup')->nullable();
            $table->string('clientType')->nullable();
            $table->string('clientCompanyName')->nullable();
            $table->string('clientName')->nullable();
            $table->string('clientPosition')->nullable();
            $table->string('clientNameCase')->nullable();
            $table->string('clientPositionCase')->nullable();
            $table->longText('settings')->nullable();
            $table->longText('saleText')->nullable();
            $table->longText('letterText')->nullable();
            $table->longText('options')->nullable();
            $table->longText('offer')->nullable();
            $table->longText('invoice')->nullable();
            $table->longText('contractDocument')->nullable();
            $table->longText('act')->nullable();
            $table->boolean('isFavorite')->nullable();








        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bx_document_deals');
    }
};
