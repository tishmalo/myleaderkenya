<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_tool_packages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_tool_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('currency', 3)->default('KES');
            $table->string('entitlement_type'); // time, quantity, one_time, permanent
            $table->unsignedInteger('entitlement_quantity')->nullable();
            $table->unsignedInteger('duration_days')->nullable();
            $table->text('fulfilment_instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['campaign_tool_id', 'is_active', 'sort_order'], 'tool_package_listing_idx');
        });

        Schema::create('campaign_tool_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_tool_request_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_tool_package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('ipay');
            $table->string('checkout_reference', 40)->unique();
            $table->string('package_name');
            $table->string('entitlement_type');
            $table->unsignedInteger('entitlement_quantity')->nullable();
            $table->unsignedInteger('duration_days')->nullable();
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('commission_rate', 5, 2)->default(20);
            $table->decimal('platform_revenue', 12, 2);
            $table->decimal('fulfilment_payable', 12, 2);
            $table->decimal('refunded_amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('KES');
            $table->string('status')->default('pending');
            $table->string('payment_reference')->nullable();
            $table->string('gateway_transaction_code')->nullable();
            $table->string('gateway_status')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('callback_received_at')->nullable();
            $table->timestamp('funded_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at'], 'tool_payment_status_idx');
        });

        Schema::create('campaign_tool_financial_ledger', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_tool_payment_id');
            $table->foreign('campaign_tool_payment_id', 'tool_financial_payment_fk')->references('id')->on('campaign_tool_payments')->cascadeOnDelete();
            $table->string('entry_type');
            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->decimal('platform_amount', 12, 2)->default(0);
            $table->decimal('fulfilment_amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('KES');
            $table->string('correlation_id', 64);
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->index(['campaign_tool_payment_id', 'occurred_at'], 'tool_financial_payment_idx');
        });

        Schema::create('candidate_campaign_tool_entitlements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_tool_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_tool_package_id')->nullable();
            $table->foreign('campaign_tool_package_id', 'candidate_tool_package_fk')->references('id')->on('campaign_tool_packages')->nullOnDelete();
            $table->foreignId('campaign_tool_payment_id')->nullable();
            $table->foreign('campaign_tool_payment_id', 'candidate_tool_payment_fk')->references('id')->on('campaign_tool_payments')->nullOnDelete();
            $table->string('tool_key')->nullable();
            $table->string('entitlement_type');
            $table->unsignedInteger('allowance')->nullable();
            $table->unsignedInteger('remaining_allowance')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('activated_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->foreignId('activated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['candidate_id', 'campaign_tool_id', 'status'], 'candidate_tool_access_idx');
        });

        Schema::table('campaign_tool_requests', function (Blueprint $table): void {
            $table->foreignId('campaign_tool_package_id')->nullable()->after('campaign_tool_id')->constrained()->nullOnDelete();
            $table->string('fulfilment_type')->nullable()->after('request_type');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_tool_requests', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('campaign_tool_package_id');
            $table->dropColumn('fulfilment_type');
        });
        Schema::dropIfExists('candidate_campaign_tool_entitlements');
        Schema::dropIfExists('campaign_tool_financial_ledger');
        Schema::dropIfExists('campaign_tool_payments');
        Schema::dropIfExists('campaign_tool_packages');
    }
};
