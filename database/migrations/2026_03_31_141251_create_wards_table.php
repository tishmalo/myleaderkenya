<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('wards', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->foreignId('constituency_id')->constrained('constituencies')->onDelete('cascade');
        $table->bigInteger('population')->nullable();
        $table->integer('registered_voters')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wards');
    }
};
