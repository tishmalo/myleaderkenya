<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_token_purchases', function (Blueprint $table): void {
            if (! Schema::hasColumn('candidate_token_purchases', 'provider')) {
                $table->string('provider')->nullable()->after('payment_method_id');
            }

            if (! Schema::hasColumn('candidate_token_purchases', 'checkout_reference')) {
                $table->string('checkout_reference', 26)->nullable()->unique()->after('provider');
            }

            if (! Schema::hasColumn('candidate_token_purchases', 'gateway_transaction_code')) {
                $table->string('gateway_transaction_code')->nullable()->after('payment_reference');
            }

            if (! Schema::hasColumn('candidate_token_purchases', 'gateway_status')) {
                $table->string('gateway_status')->nullable()->after('gateway_transaction_code');
            }

            if (! Schema::hasColumn('candidate_token_purchases', 'gateway_response')) {
                $table->json('gateway_response')->nullable()->after('gateway_status');
            }

            if (! Schema::hasColumn('candidate_token_purchases', 'callback_received_at')) {
                $table->timestamp('callback_received_at')->nullable()->after('gateway_response');
            }
        });
    }

    public function down(): void
    {
        Schema::table('candidate_token_purchases', function (Blueprint $table): void {
            if (Schema::hasColumn('candidate_token_purchases', 'checkout_reference')) {
                $table->dropUnique('candidate_token_purchases_checkout_reference_unique');
            }

            foreach (['callback_received_at', 'gateway_response', 'gateway_status', 'gateway_transaction_code', 'checkout_reference', 'provider'] as $column) {
                if (Schema::hasColumn('candidate_token_purchases', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
