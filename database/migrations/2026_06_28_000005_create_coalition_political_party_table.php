<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coalition_political_party', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coalition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('political_party_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['coalition_id', 'political_party_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coalition_political_party');
    }
};
