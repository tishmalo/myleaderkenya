<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spam_rules', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index(); // keyword|domain|email|phone|ip|regex
            $table->string('value');
            $table->boolean('enabled')->default(true);
            $table->string('source')->default('admin'); // admin|config
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['type', 'value']);
        });

        Schema::create('spam_samples', function (Blueprint $table) {
            $table->id();
            $table->json('payload')->nullable();
            $table->string('text_hash')->nullable()->index();
            $table->string('reason')->nullable()->index();
            $table->string('ip')->nullable()->index();
            $table->string('source')->default('request'); // request|admin|reported
            $table->foreignId('campaign_tool_request_id')->nullable()->constrained('campaign_tool_requests')->nullOnDelete();
            $table->timestamps();

            $table->unique('text_hash');
        });

        Schema::create('spam_ip_overrides', function (Blueprint $table) {
            $table->id();
            $table->string('ip')->unique();
            $table->string('action')->default('allow');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spam_ip_overrides');
        Schema::dropIfExists('spam_samples');
        Schema::dropIfExists('spam_rules');
    }
};