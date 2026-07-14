<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_call_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('voter_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('voter_name')->nullable();
            $table->string('voter_phone')->nullable();
            $table->string('outcome');
            $table->text('notes')->nullable();
            $table->timestamp('callback_at')->nullable();
            $table->string('scope_type')->nullable();
            $table->string('scope_column')->nullable();
            $table->string('scope_value')->nullable();
            $table->timestamp('called_at');
            $table->timestamps();

            $table->index(['candidate_id', 'outcome']);
            $table->index(['candidate_id', 'called_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_call_logs');
    }
};
