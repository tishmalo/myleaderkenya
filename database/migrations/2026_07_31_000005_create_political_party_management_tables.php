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
            $table->foreignId('political_party_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['party_admin', 'party_staff'])->default('party_staff');
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->timestamps();
            $table->unique(['political_party_id', 'user_id']);
        });
        Schema::create('political_party_account_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('political_party_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 50);
            $table->string('party_title');
            $table->string('authorization_document');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['political_party_id', 'status']);
        });
        Schema::create('political_party_candidate_claims', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('political_party_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['political_party_id', 'status']);
        });
        Schema::create('political_party_token_wallets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('political_party_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('balance')->default(0);
            $table->timestamps();
        });
        Schema::create('political_party_token_purchases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('political_party_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('candidate_token_package_id')->nullable()->constrained()->nullOnDelete();
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
            $table->foreignId('political_party_id')->constrained()->cascadeOnDelete();
            $table->foreignId('political_party_token_wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('candidate_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('political_party_token_purchase_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->integer('amount');
            $table->unsignedBigInteger('balance_before');
            $table->unsignedBigInteger('balance_after');
            $table->json('metadata')->nullable();
            $table->timestamp('finalized_at');
            $table->timestamps();
            $table->index(['political_party_id', 'created_at']);
        });
        Schema::create('political_party_token_transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('political_party_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('party_transaction_id')->constrained('political_party_token_transactions')->cascadeOnDelete();
            $table->foreignId('candidate_transaction_id')->constrained('candidate_token_transactions')->cascadeOnDelete();
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
