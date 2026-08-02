<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_token_wallets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('balance')->default(0);
            $table->timestamp('initial_granted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('candidate_token_packages', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('token_amount');
            $table->unsignedInteger('price');
            $table->string('currency', 3)->default('KES');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('candidate_token_rates', function (Blueprint $table): void {
            $table->id();
            $table->string('action_key')->unique();
            $table->string('label');
            $table->enum('calculation_type', ['fixed', 'per_recipient', 'per_sms_unit']);
            $table->unsignedInteger('token_amount')->default(1);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('candidate_token_purchases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('candidate_token_package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('package_name');
            $table->unsignedInteger('token_amount');
            $table->unsignedInteger('price');
            $table->string('currency', 3)->default('KES');
            $table->string('payment_reference')->nullable();
            $table->string('status')->default('credited');
            $table->timestamp('credited_at')->nullable();
            $table->timestamps();
        });

        Schema::create('candidate_token_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_token_wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('candidate_token_purchase_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableMorphs('tokenable');
            $table->string('type');
            $table->string('status')->default('completed');
            $table->string('action_key')->nullable();
            $table->string('action_label')->nullable();
            $table->string('calculation_type')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('unit_tokens')->default(1);
            $table->integer('amount');
            $table->unsignedInteger('balance_before');
            $table->unsignedInteger('balance_after');
            $table->json('metadata')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
        });

        Schema::table('candidate_sms_messages', function (Blueprint $table): void {
            $table->foreignId('token_transaction_id')->nullable()->after('status')->constrained('candidate_token_transactions')->nullOnDelete();
            $table->unsignedInteger('sms_character_count')->default(0)->after('recipient_count');
            $table->string('sms_encoding')->nullable()->after('sms_character_count');
            $table->unsignedInteger('sms_segment_count')->default(0)->after('sms_encoding');
            $table->unsignedInteger('sms_unit_count')->default(0)->after('sms_segment_count');
            $table->unsignedInteger('token_cost')->default(0)->after('sms_unit_count');
        });

        Schema::create('candidate_sms_balance_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider')->default('infobip');
            $table->unsignedInteger('requested_amount')->nullable();
            $table->text('message')->nullable();
            $table->json('provider_balance_snapshot')->nullable();
            $table->string('status')->default('new');
            $table->text('admin_notes')->nullable();
            $table->timestamp('followed_up_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_sms_balance_requests');

        Schema::table('candidate_sms_messages', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('token_transaction_id');
            $table->dropColumn([
                'sms_character_count',
                'sms_encoding',
                'sms_segment_count',
                'sms_unit_count',
                'token_cost',
            ]);
        });

        Schema::dropIfExists('candidate_token_transactions');
        Schema::dropIfExists('candidate_token_purchases');
        Schema::dropIfExists('candidate_token_rates');
        Schema::dropIfExists('candidate_token_packages');
        Schema::dropIfExists('candidate_token_wallets');
    }
};


