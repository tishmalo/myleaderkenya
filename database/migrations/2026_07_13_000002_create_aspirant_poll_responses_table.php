<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aspirant_poll_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aspirant_poll_id')->constrained('aspirant_polls')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('option_index');
            $table->timestamps();

            $table->unique(['aspirant_poll_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aspirant_poll_responses');
    }
};
