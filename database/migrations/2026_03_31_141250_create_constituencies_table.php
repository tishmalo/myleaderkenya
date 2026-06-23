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
    Schema::create('constituencies', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->foreignId('county_id')->constrained('counties')->onDelete('cascade');
        $table->bigInteger('population')->nullable();
        $table->integer('number_of_seats')->default(1);
        $table->string('position_name')->nullable(); // e.g. "Member of Parliament"
        $table->timestamps();

        $table->unique(['name', 'county_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('constituencies');
    }
};
