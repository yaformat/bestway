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

        Schema::table('hotel_info_blocks', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('hotel_buildings', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('hotel_services', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('hotel_service_prices', function (Blueprint $table) {
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotel_info_blocks', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('hotel_buildings', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('hotel_services', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('hotel_service_prices', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

    }
};