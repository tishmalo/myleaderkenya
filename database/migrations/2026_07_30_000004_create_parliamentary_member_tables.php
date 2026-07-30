<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parliament_import_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('import_key')->unique();
            $table->string('status')->default('pending')->index();
            $table->unsignedInteger('members_received')->default(0);
            $table->unsignedInteger('members_saved')->default(0);
            $table->string('failure_code')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('parliament_members', function (Blueprint $table): void {
            $table->id();
            $table->string('external_slug')->unique();
            $table->string('source_name')->index();
            $table->string('normalized_name')->index();
            $table->string('source_url')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('house', 60)->nullable()->index();
            $table->string('role')->nullable()->index();
            $table->string('constituency')->nullable()->index();
            $table->string('party')->nullable()->index();
            $table->string('position_type')->nullable();
            $table->longText('biography')->nullable();
            $table->unsignedInteger('speeches_last_year')->nullable();
            $table->unsignedInteger('speeches_total')->nullable();
            $table->unsignedInteger('bills_total')->nullable();
            $table->unsignedInteger('bills_pages')->nullable();
            $table->json('raw_payload')->nullable();
            $table->string('detail_status')->default('missing')->index();
            $table->string('failure_code')->nullable();
            $table->timestamp('detail_fetched_at')->nullable()->index();
            $table->foreignId('candidate_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('match_method')->nullable()->index();
            $table->unsignedTinyInteger('matched_token_count')->default(0);
            $table->foreignId('linked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('linked_at')->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['detail_status', 'candidate_id']);
            $table->index(['candidate_id', 'is_published', 'detail_status'], 'pm_public_profile_idx');
            $table->index(['match_method', 'is_published'], 'pm_match_publish_idx');
        });

        Schema::create('parliament_member_committees', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parliament_member_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('normalized_name')->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['parliament_member_id', 'normalized_name'], 'pm_committee_unique');
            $table->index(['parliament_member_id', 'sort_order'], 'pm_committee_order_idx');
        });

        Schema::create('parliament_member_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parliament_member_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30)->index();
            $table->date('occurred_on')->nullable()->index();
            $table->string('title')->index();
            $table->string('decision')->nullable()->index();
            $table->string('source_url')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['parliament_member_id', 'type', 'occurred_on'], 'pm_activity_member_type_date_idx');
            $table->index(['type', 'occurred_on'], 'pm_activity_type_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parliament_member_activities');
        Schema::dropIfExists('parliament_member_committees');
        Schema::dropIfExists('parliament_members');
        Schema::dropIfExists('parliament_import_runs');
    }
};