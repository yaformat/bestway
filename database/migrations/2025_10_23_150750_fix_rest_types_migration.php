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
        Schema::table('hotels', function (Blueprint $table) {
            $table->json('rest_types')->nullable()->after('hotel_type');
        });

        Schema::dropIfExists('entity_rest_types');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn('rest_types');
        });

        Schema::create('entity_rest_types', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type'); // hotel, resort, tour и т.д.
            $table->unsignedBigInteger('entity_id');
            $table->foreignId('rest_type_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['entity_type', 'entity_id']);
        });
    }
};