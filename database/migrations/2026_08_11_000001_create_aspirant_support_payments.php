<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_token_purchases', function (Blueprint $table): void {
            $table->string('purchaser_name')->nullable()->after('user_id');
            $table->string('objective', 30)->default('my_kitty')->after('purchaser_name');
            $table->string('kitty_type', 40)->nullable()->after('objective');
        });

        Schema::create('aspirant_support_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_token_package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('supporter_name');
            $table->string('supporter_email');
            $table->string('supporter_phone', 30);
            $table->text('message');
            $table->string('provider')->default('ipay');
            $table->string('checkout_reference', 40)->unique();
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('platform_fee_rate', 5, 2)->default(20);
            $table->decimal('platform_fee_amount', 12, 2);
            $table->decimal('aspirant_amount', 12, 2);
            $table->string('currency', 3)->default('KES');
            $table->string('payment_reference')->nullable();
            $table->string('gateway_transaction_code')->nullable();
            $table->string('gateway_status')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('callback_received_at')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->text('aspirant_reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->foreignId('replied_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['candidate_id', 'status', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aspirant_support_payments');
        Schema::table('user_token_purchases', function (Blueprint $table): void {
            $table->dropColumn(['purchaser_name', 'objective', 'kitty_type']);
        });
    }
};
