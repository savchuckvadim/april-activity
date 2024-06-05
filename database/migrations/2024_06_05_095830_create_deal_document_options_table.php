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
        Schema::create('deal_document_options', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('dealId')->nullable();   //id модели в online сохраненной сделки - deals
            $table->foreignId('dealDocumentFavoriteId')->nullable();
            //from style  settings
            $table->longText('salePhrase');
            $table->longText('withStamp');
            $table->longText('isPriceFirst');
            $table->longText('withManager');
            $table->longText('iblocksStyle');
            $table->longText('describStyle');
            $table->longText('otherStyle');
            //from price  settings
            $table->longText('priceDiscount')->nullable(); //"name":"discount","show":"Показать скидку","unshow":"Скрыт
            $table->longText('priceYear');  //{"name":"year","show":"Показать сумму за весь период обслуживания","unshow":"
            $table->longText('priceDefault');  //{"name":"price","show":"Показать цену по прайсу","unshow":"Скрыть цену по прайс
            $table->longText('priceSupply');  //{"name":"supply","show":"Показать ОД развёрнуто","unshow":"Сократить информа
            $table->longText('priceOptions');  //{"isInvoice":false,"isDefaultShow":true,"isTable":true,"isOneMeasure":true,"isDiscountSho
            $table->longText('otherPrice');

            $table->longText('otherSettings')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deal_document_options');
    }
};
