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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Название домена');
            $table->string('domain')->unique()->comment('Доменное имя');
            $table->text('description')->nullable()->comment('Описание домена');
            $table->boolean('is_default')->default(false)->comment('Домен по умолчанию');
            $table->boolean('is_active')->default(true)->comment('Активность домена');
            $table->integer('sort_order')->default(0)->comment('Порядок сортировки');
            $table->timestamps();
            $table->softDeletes();
            
            // Индексы
            $table->index(['is_active', 'is_default']);
            $table->index('domain');
            $table->index('sort_order');
            
            $table->comment('Домены сайта');
        });

        Schema::create('domain_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained()->cascadeOnDelete()->comment('ID домена');
            $table->string('key')->comment('Ключ настройки');
            $table->longText('value')->nullable()->comment('Значение настройки');
            $table->string('type')->default(\App\Models\DomainSetting::TYPE_STRING)->comment('Тип значения');
            $table->text('description')->nullable()->comment('Описание настройки');
            $table->boolean('is_public')->default(false)->comment('Доступна для фронтенда');
            $table->timestamps();
            
            // Уникальный ключ для домен-ключ
            $table->unique(['domain_id', 'key']);
            
            // Индексы
            $table->index(['domain_id', 'key']);
            $table->index('is_public');
            
            $table->comment('Настройки доменов');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //Schema::dropIfExists('domains');
        //Schema::dropIfExists('domain_settings');
    }
};