<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_pulse_jobs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('job_ref')->unique();
            $table->string('engine_job_id', 36)->nullable()->unique();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('keywords');
            $table->date('date_from');
            $table->date('date_to');
            $table->unsignedInteger('requested_limit')->default(100);
            $table->string('status', 40)->default('submitting')->index();
            $table->boolean('partial')->default(false);
            $table->json('summary')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('last_synced_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable()->index();
            $table->timestamps();

            $table->index(['candidate_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_pulse_jobs');
    }
};