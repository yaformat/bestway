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

        Schema::create('hotel_info_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
            $table->string('block_key'); // ключ из конфига
            $table->longText('content')->nullable(); // HTML контент
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->unique(['hotel_id', 'block_key']);
            $table->index(['hotel_id', 'block_key', 'is_active']);
        });

        Schema::create('hotel_buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['hotel_id', 'sort_order']);
        });

        Schema::create('hotel_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['hotel_id', 'sort_order']);
        });

        Schema::create('hotel_service_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_service_id')->constrained()->onDelete('cascade');
            $table->foreignId('booking_period_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->string('currency', 3);
            $table->string('pricing_type')->default('per_unit'); // per_unit, per_person, per_day
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['hotel_service_id', 'booking_period_id']);
        });

        Schema::table('hotels', function (Blueprint $table) {
            $table->string('currency', 3)->default('KGS'); // Основная валюта отеля
            $table->string('hotel_type')->default('hotel'); // Тип отеля
            $table->longText('description')->nullable()->change(); // Для HTML контента
            $table->dropColumn(['stars']); // Убираем звезды, используем тип отеля
        });

        Schema::table('hotel_rooms', function (Blueprint $table) {
            $table->foreignId('hotel_building_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_info_blocks');
        Schema::dropIfExists('hotel_buildings');
        Schema::dropIfExists('hotel_services');
        Schema::dropIfExists('hotel_service_prices');

        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn(['currency', 'hotel_type']);
            $table->integer('stars')->nullable();
        });

        Schema::table('hotel_rooms', function (Blueprint $table) {
            $table->dropForeign(['hotel_building_id']);
            $table->dropColumn('hotel_building_id');
        });
    }
};