<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_token_wallets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('balance')->default(0);
            $table->timestamps();
        });

        Schema::create('user_token_purchases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_token_package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider')->default('ipay');
            $table->string('checkout_reference', 40)->unique();
            $table->string('package_name');
            $table->unsignedBigInteger('token_amount');
            $table->decimal('price', 12, 2);
            $table->string('currency', 3)->default('KES');
            $table->string('payment_reference')->nullable();
            $table->string('gateway_transaction_code')->nullable();
            $table->string('gateway_status')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('callback_received_at')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('credited_at')->nullable();
            $table->timestamps();
        });

        Schema::create('user_token_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_token_wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_token_purchase_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableMorphs('tokenable');
            $table->string('type');
            $table->string('status')->default('completed');
            $table->string('action_key')->nullable();
            $table->string('action_label');
            $table->bigInteger('amount');
            $table->unsignedBigInteger('balance_before');
            $table->unsignedBigInteger('balance_after');
            $table->json('metadata')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('campaign_tools', function (Blueprint $table): void {
            $table->unsignedInteger('sponsorship_token_cost')->default(0)->after('sort_order');
        });

        Schema::table('campaign_tool_requests', function (Blueprint $table): void {
            $table->unsignedBigInteger('tokens_required')->default(0)->after('status');
            $table->string('payment_status')->default('not_required')->after('tokens_required');
            $table->foreignId('user_token_transaction_id')->nullable()->after('payment_status')->constrained()->nullOnDelete();
            $table->timestamp('paid_at')->nullable()->after('user_token_transaction_id');
            $table->timestamp('refunded_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_tool_requests', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_token_transaction_id');
            $table->dropColumn(['tokens_required', 'payment_status', 'paid_at', 'refunded_at']);
        });
        Schema::table('campaign_tools', fn (Blueprint $table) => $table->dropColumn('sponsorship_token_cost'));
        Schema::dropIfExists('user_token_transactions');
        Schema::dropIfExists('user_token_purchases');
        Schema::dropIfExists('user_token_wallets');
    }
};