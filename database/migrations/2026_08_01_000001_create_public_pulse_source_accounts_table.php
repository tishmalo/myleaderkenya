<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('public_pulse_source_accounts')) {
            return;
        }

        Schema::create('public_pulse_source_accounts', function (Blueprint $table): void {
            $table->id();
            $table->string('source_key')->default('x')->index();
            $table->string('provider')->default('x_twscrape')->index();
            $table->string('label');
            $table->string('username')->nullable()->index();
            $table->text('encrypted_session_payload');
            $table->string('status')->default('needs_replacement')->index();
            $table->timestamp('last_health_check_at')->nullable()->index();
            $table->timestamp('last_success_at')->nullable()->index();
            $table->unsignedInteger('failure_count')->default(0);
            $table->unsignedInteger('consecutive_failure_count')->default(0)->index();
            $table->string('last_error_code')->nullable()->index();
            $table->text('last_error_message')->nullable();
            $table->unsignedInteger('last_result_count')->nullable();
            $table->decimal('median_result_ratio', 6, 3)->nullable();
            $table->timestamp('cooldown_until')->nullable()->index();
            $table->timestamp('issue_notified_at')->nullable();
            $table->timestamp('replaced_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['provider', 'status', 'cooldown_until'], 'pulse_accounts_provider_status_cooldown_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_pulse_source_accounts');
    }
};
