<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('political_party_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('political_party_id')->constrained(indexName: 'ppu_party_fk')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained(indexName: 'ppu_user_fk')->cascadeOnDelete();
            $table->enum('role', ['party_admin', 'party_staff'])->default('party_staff');
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->timestamps();
            $table->unique(['political_party_id', 'user_id'], 'pp_party_user_uq');
        });
        Schema::create('political_party_account_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('political_party_id')->constrained(indexName: 'ppar_party_fk')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained(indexName: 'ppar_user_fk')->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 50);
            $table->string('party_title');
            $table->string('authorization_document');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users', indexName: 'ppar_reviewer_fk')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['political_party_id', 'status'], 'pp_party_status_idx');
        });
        Schema::create('political_party_candidate_claims', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('political_party_id')->constrained(indexName: 'ppcc_party_fk')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained(indexName: 'ppcc_candidate_fk')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users', indexName: 'ppcc_requester_fk')->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users', indexName: 'ppcc_reviewer_fk')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['political_party_id', 'status'], 'pp_party_status_idx');
        });
        Schema::create('political_party_token_wallets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('political_party_id')->unique('pp_wallet_party_uq')->constrained(indexName: 'pptw_party_fk')->cascadeOnDelete();
            $table->unsignedBigInteger('balance')->default(0);
            $table->timestamps();
        });
        Schema::create('political_party_token_purchases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('political_party_id')->constrained(indexName: 'pptp_party_fk')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained(indexName: 'pptp_user_fk')->nullOnDelete();
            $table->foreignId('candidate_token_package_id')->nullable()->constrained(indexName: 'pp_purchase_package_fk')->nullOnDelete();
            $table->string('provider')->default('ipay');
            $table->string('checkout_reference')->unique();
            $table->string('package_name');
            $table->unsignedInteger('token_amount');
            $table->unsignedInteger('price');
            $table->string('currency', 3)->default('KES');
            $table->string('payment_reference')->nullable();
            $table->string('gateway_transaction_code')->nullable();
            $table->string('gateway_status')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('callback_received_at')->nullable();
            $table->enum('status', ['pending', 'credited', 'failed'])->default('pending');
            $table->timestamp('credited_at')->nullable();
            $table->timestamps();
        });
        Schema::create('political_party_token_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('political_party_id')->constrained(indexName: 'pptx_party_fk')->cascadeOnDelete();
            $table->foreignId('political_party_token_wallet_id')->constrained(indexName: 'pptx_wallet_fk')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained(indexName: 'pptx_user_fk')->nullOnDelete();
            $table->foreignId('candidate_id')->nullable()->constrained(indexName: 'pptx_candidate_fk')->nullOnDelete();
            $table->foreignId('political_party_token_purchase_id')->nullable()->constrained(indexName: 'pptx_purchase_fk')->nullOnDelete();
            $table->string('type');
            $table->integer('amount');
            $table->unsignedBigInteger('balance_before');
            $table->unsignedBigInteger('balance_after');
            $table->json('metadata')->nullable();
            $table->timestamp('finalized_at');
            $table->timestamps();
            $table->index(['political_party_id', 'created_at'], 'pp_tx_party_created_idx');
        });
        Schema::create('political_party_token_transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('political_party_id')->constrained(indexName: 'pptt_party_fk')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained(indexName: 'pptt_candidate_fk')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained(indexName: 'pptt_user_fk')->nullOnDelete();
            $table->foreignId('party_transaction_id')->constrained('political_party_token_transactions', indexName: 'pptt_party_tx_fk')->cascadeOnDelete();
            $table->foreignId('candidate_transaction_id')->constrained('candidate_token_transactions', indexName: 'pptt_candidate_tx_fk')->cascadeOnDelete();
            $table->unsignedInteger('amount');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('political_party_token_transfers');
        Schema::dropIfExists('political_party_token_transactions');
        Schema::dropIfExists('political_party_token_purchases');
        Schema::dropIfExists('political_party_token_wallets');
        Schema::dropIfExists('political_party_candidate_claims');
        Schema::dropIfExists('political_party_account_requests');
        Schema::dropIfExists('political_party_user');
    }
};
