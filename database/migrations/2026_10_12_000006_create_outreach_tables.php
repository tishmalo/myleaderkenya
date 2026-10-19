<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin outreach: one campaign per send (email or SMS), one row per recipient so
 * every person's delivery can be reported on.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outreach_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->string('channel', 10);
            $table->string('audience', 60);
            $table->string('subject')->nullable();
            $table->longText('body');
            $table->string('status', 20)->default('queued');
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['channel', 'status'], 'outreach_campaigns_channel_status_idx');
        });

        Schema::create('outreach_recipients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_id')->constrained('outreach_campaigns')->cascadeOnDelete();
            $table->nullableMorphs('recipient');
            $table->string('name')->nullable();
            $table->text('email')->nullable();
            $table->text('phone')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->text('error')->nullable();
            $table->string('rendered_subject')->nullable();
            $table->longText('rendered_body')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'status'], 'outreach_recipients_campaign_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outreach_recipients');
        Schema::dropIfExists('outreach_campaigns');
    }
};
