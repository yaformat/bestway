<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::dropIfExists('directions');
        Schema::dropIfExists('resorts');
        Schema::dropIfExists('hotels');
        Schema::dropIfExists('hotel_rooms');
        Schema::dropIfExists('booking_periods');
        Schema::dropIfExists('rest_types');
        Schema::dropIfExists('entity_rest_types');
        Schema::dropIfExists('tours');
        Schema::dropIfExists('tour_programs');
        Schema::dropIfExists('transfers');
        Schema::dropIfExists('excursions');
        Schema::dropIfExists('excursion_programs');
        Schema::dropIfExists('excursions');

        Schema::create('directions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->decimal('latitude', 8, 8)->nullable();
            $table->decimal('longitude', 8, 8)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')->references('id')->on('directions')->onDelete('cascade');
            $table->index(['parent_id', 'sort_order']);
            $table->index('is_active');
        });

        Schema::create('resorts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('direction_id');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->decimal('latitude', 8, 8)->nullable();
            $table->decimal('longitude', 8, 8)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('direction_id')->references('id')->on('directions')->onDelete('cascade');
            $table->index('direction_id');
            $table->index('is_active');
        });

        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('direction_id');
            $table->unsignedBigInteger('resort_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->decimal('latitude', 8, 8)->nullable();
            $table->decimal('longitude', 8, 8)->nullable();
            $table->decimal('rating', 2, 1)->default(0);
            $table->integer('stars')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('direction_id')->references('id')->on('directions')->onDelete('cascade');
            $table->foreign('resort_id')->references('id')->on('resorts')->onDelete('cascade');
            $table->index(['direction_id', 'resort_id']);
            $table->index('is_active');
        });

        Schema::create('hotel_rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotel_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('capacity')->default(1);
            $table->integer('beds_count')->default(1);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('cascade');
            $table->index('hotel_id');
            $table->index('is_active');
        });

        Schema::create('booking_periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotel_id');
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('cascade');
            $table->index('hotel_id');
            $table->index(['start_date', 'end_date']);
            $table->index('is_active');
        });

        Schema::create('hotel_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotel_room_id');
            $table->unsignedBigInteger('booking_period_id');
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->integer('min_nights')->default(1);
            $table->integer('max_nights')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('hotel_room_id')->references('id')->on('hotel_rooms')->onDelete('cascade');
            $table->foreign('booking_period_id')->references('id')->on('booking_periods')->onDelete('cascade');
            $table->unique(['hotel_room_id', 'booking_period_id']);
            $table->index(['hotel_room_id', 'booking_period_id']);
        });

        Schema::create('rest_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });

        Schema::create('entity_rest_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rest_type_id');
            $table->morphs('entity'); // entity_type, entity_id
            $table->timestamps();

            $table->foreign('rest_type_id')->references('id')->on('rest_types')->onDelete('cascade');
            //$table->index(['entity_type', 'entity_id']);
            $table->unique(['rest_type_id', 'entity_type', 'entity_id'], 'unique_rest_type_entity');
        });

        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('direction_id');
            $table->integer('duration_days')->default(1);
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('difficulty_level')->default('easy'); // easy, medium, hard
            $table->integer('group_size_min')->default(1);
            $table->integer('group_size_max')->default(20);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('direction_id')->references('id')->on('directions')->onDelete('cascade');
            $table->index('direction_id');
            $table->index('is_active');
        });

        Schema::create('tour_programs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tour_id');
            $table->integer('day');
            $table->string('title');
            $table->text('description')->nullable();
            $table->time('time')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tour_id')->references('id')->on('tours')->onDelete('cascade');
            $table->index(['tour_id', 'day', 'sort_order']);
        });

        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type'); // airport, hotel, city, etc.
            $table->string('from_location');
            $table->string('to_location');
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->integer('capacity')->default(1);
            $table->integer('duration_minutes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('is_active');
        });

        Schema::create('excursions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('direction_id');
            $table->decimal('duration_hours', 4, 2)->default(1);
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->integer('group_size_min')->default(1);
            $table->integer('group_size_max')->default(20);
            $table->string('difficulty_level')->default('easy');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('direction_id')->references('id')->on('directions')->onDelete('cascade');
            $table->index('direction_id');
            $table->index('is_active');
        });

        Schema::create('excursion_programs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('excursion_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('excursion_id')->references('id')->on('excursions')->onDelete('cascade');
            $table->index(['excursion_id', 'sort_order']);
        });

        
    }

    public function down(): void
    {
        // Schema::dropIfExists('directions');
        // Schema::dropIfExists('resorts');
        // Schema::dropIfExists('hotels');
        // Schema::dropIfExists('hotel_rooms');
        // Schema::dropIfExists('booking_periods');
        // Schema::dropIfExists('rest_types');
        // Schema::dropIfExists('entity_rest_types');
        // Schema::dropIfExists('tours');
        // Schema::dropIfExists('tour_programs');
        // Schema::dropIfExists('transfers');
        // Schema::dropIfExists('excursions');
        // Schema::dropIfExists('excursion_programs');
        // Schema::dropIfExists('excursions');

    }
};