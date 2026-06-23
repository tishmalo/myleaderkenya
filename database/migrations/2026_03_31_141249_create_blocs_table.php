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
    Schema::create('blocs', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique();
        $table->json('tribes')->nullable();           // array of tribes
        $table->integer('tribe_population')->nullable();
        $table->json('voting_patterns')->nullable();  // e.g. [{candidate: "Ruto", year: 2022, votes: 45000}, ...]
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocs');
    }
};
