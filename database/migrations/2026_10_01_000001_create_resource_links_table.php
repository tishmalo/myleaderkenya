<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('platform');
            $table->string('url', 2048);
            $table->foreignId('county_id')->constrained('counties')->cascadeOnDelete();
            $table->foreignId('constituency_id')->nullable()->constrained('constituencies')->nullOnDelete();
            $table->foreignId('ward_id')->nullable()->constrained('wards')->nullOnDelete();
            $table->foreignId('political_party_id')->nullable()->constrained('political_parties')->nullOnDelete();
            $table->foreignId('candidate_id')->nullable()->constrained('candidates')->cascadeOnDelete();
            $table->unsignedBigInteger('followers')->nullable();
            $table->text('comment')->nullable();
            $table->string('approval_status')->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['approval_status', 'county_id'], 'resource_links_status_county_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_links');
    }
};
