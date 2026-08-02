<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_call_scripts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('script');
            $table->string('callback_priority')->default('undecided');
            $table->string('scope_type')->nullable();
            $table->string('scope_column')->nullable();
            $table->string('scope_value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_call_scripts');
    }
};
