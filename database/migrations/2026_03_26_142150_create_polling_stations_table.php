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
    Schema::create('polling_stations', function (Blueprint $table) {
        $table->id();
        $table->string('county');
        $table->string('constituency');
        $table->string('office');
        $table->string('near_landmark')->nullable();
        $table->integer('distance_to_office')->default(0);
        $table->decimal('lat', 10, 8);
        $table->decimal('lon', 11, 8);
        $table->boolean('is_user_added')->default(false);   // to distinguish user-added stations
        $table->timestamps();

        $table->index(['lat', 'lon']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polling_stations');
    }
};
