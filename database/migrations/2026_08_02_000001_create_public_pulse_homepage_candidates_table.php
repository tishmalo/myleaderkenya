<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('public_pulse_homepage_candidates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_pulse_homepage_candidates');
    }
};
