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
    Schema::create('counties', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique();
        $table->foreignId('bloc_id')->constrained('blocs')->onDelete('cascade');
        $table->string('area')->nullable();
        $table->bigInteger('population')->nullable();
        $table->string('capital')->nullable();
        $table->integer('registered_voters')->nullable();
        $table->string('postal_abbreviation')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counties');
    }
};
