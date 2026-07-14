<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table): void {
            if (! Schema::hasColumn('candidates', 'claim_token_hash')) {
                $table->string('claim_token_hash', 64)->nullable()->after('user_id')->index();
            }

            if (! Schema::hasColumn('candidates', 'claim_token_expires_at')) {
                $table->timestamp('claim_token_expires_at')->nullable()->after('claim_token_hash');
            }

            if (! Schema::hasColumn('candidates', 'claim_sent_at')) {
                $table->timestamp('claim_sent_at')->nullable()->after('claim_token_expires_at');
            }

            if (! Schema::hasColumn('candidates', 'claimed_at')) {
                $table->timestamp('claimed_at')->nullable()->after('claim_sent_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table): void {
            foreach (['claimed_at', 'claim_sent_at', 'claim_token_expires_at', 'claim_token_hash'] as $column) {
                if (Schema::hasColumn('candidates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
