<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_approval_scores', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->string('profile_slug');
            $table->decimal('approval_score', 5, 2);
            $table->string('source')->default('politiq');
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->unique('candidate_id');
            $table->index('profile_slug');
            $table->index('fetched_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_approval_scores');
    }
};