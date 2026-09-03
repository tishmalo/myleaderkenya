<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_transfer_runs', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index(); // import|export
            $table->string('status')->default('pending')->index(); // pending|running|complete|failed
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source_path')->nullable();
            $table->string('result_path')->nullable();
            $table->string('download_name')->nullable();
            $table->json('filters')->nullable();
            $table->unsignedInteger('imported_count')->default(0);
            $table->unsignedInteger('linked_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->unsignedInteger('exported_count')->default(0);
            $table->json('errors')->nullable();
            $table->string('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_transfer_runs');
    }
};