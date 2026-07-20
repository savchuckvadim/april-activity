<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Маркетплейс Битрикс24: коды подключения портала к внешнему сервису April.
     *
     * Приложение «Менеджер Гарант» публикуется как клиентский интерфейс
     * внешнего сервиса: партнёр устанавливает его из Маркета и вводит в
     * кабинете код, полученный от вендора по договору. Код выпускает админ
     * (админка → «Коды подключения»), система отправляет его на e-mail.
     *
     * Почему отдельная таблица, а не personal_access_tokens: код выпускается
     * ДО того, как портал известен (партнёр ещё не установил приложение),
     * а хранить нужно адресата, организацию, кем выдан/отозван и какой портал
     * в итоге погасил код — в модель tokenable_type/tokenable_id это не ложится.
     *
     * САМ КОД НЕ ХРАНИТСЯ: в code_hash лежит sha256-hex (как в
     * personal_access_tokens), открытый текст показывается админу один раз
     * при выпуске и уходит в письмо. Поэтому «отправить повторно тот же код»
     * невозможно — только выпустить новый.
     */
    public function up(): void
    {
        Schema::create('portal_invites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();

            // sha256-hex от нормализованного кода (64 символа), сам код не хранится
            $table->string('code_hash', 64)->unique();
            // видимая часть кода для поиска в списке админки, напр. GRNT-AB12
            $table->string('code_prefix', 16);

            // кому выпущен; клиент создаётся при выпуске кода по e-mail
            $table->unsignedBigInteger('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');

            $table->string('email'); // адрес отправки (может отличаться от clients.email)
            $table->string('organization')->nullable();

            $table->string('product_code')->default('sales'); // sales | service
            // ставить сущности сразу при погашении или ждать действий клиента
            // (мастер настройки соберёт данные и запустит установку сам)
            $table->boolean('auto_provision')->default(true);

            $table->string('status')->default('issued'); // issued | sent | redeemed | revoked | expired
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamp('revoked_at')->nullable();

            // какой портал погасил код (set null — историю выпуска не теряем)
            $table->unsignedBigInteger('redeemed_portal_id')->nullable();
            $table->foreign('redeemed_portal_id')->references('id')->on('portals')->onDelete('set null');

            $table->string('issued_by')->nullable();  // login супер-пользователя
            $table->string('revoked_by')->nullable();
            $table->text('note')->nullable();

            $table->index('status');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_invites');
    }
};
