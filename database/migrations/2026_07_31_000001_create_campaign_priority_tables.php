<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_priority_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->string('icon', 80)->default('fas fa-bullseye');
            $table->string('description', 500)->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('candidate_campaign_priorities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('campaign_priority_category_id');
            $table->foreign('campaign_priority_category_id', 'ccp_category_fk')->references('id')->on('campaign_priority_categories')->restrictOnDelete();
            $table->text('manifesto');
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['candidate_id', 'campaign_priority_category_id'], 'ccp_candidate_category_unique');
            $table->index(['candidate_id', 'status', 'sort_order'], 'ccp_public_profile_idx');
            $table->index(['status', 'updated_at'], 'ccp_review_queue_idx');
        });

        $now = now();
        DB::table('campaign_priority_categories')->insert([
            ['name' => 'Community Development', 'slug' => 'community-development', 'icon' => 'fas fa-seedling', 'description' => 'Community services, local development and inclusive growth.', 'sort_order' => 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Jobs & Empowerment', 'slug' => 'jobs-empowerment', 'icon' => 'fas fa-briefcase', 'description' => 'Employment, enterprise and economic opportunity.', 'sort_order' => 20, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Education & Youth', 'slug' => 'education-youth', 'icon' => 'fas fa-graduation-cap', 'description' => 'Education, skills development and youth opportunity.', 'sort_order' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Accountable Leadership', 'slug' => 'accountable-leadership', 'icon' => 'fas fa-shield-halved', 'description' => 'Integrity, transparency and responsible public leadership.', 'sort_order' => 40, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_campaign_priorities');
        Schema::dropIfExists('campaign_priority_categories');
    }
};