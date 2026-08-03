<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_sms_messages', function (Blueprint $table): void {
            $table->string('recipient_source')->default('registered_voters')->after('message');
            $table->foreignId('support_group_type_id')->nullable()->after('recipient_source')->constrained()->nullOnDelete();
            $table->timestamp('privacy_acknowledged_at')->nullable()->after('support_group_type_id');
            $table->index(['candidate_id', 'recipient_source', 'support_group_type_id'], 'candidate_sms_recipient_source_idx');
        });
    }

    public function down(): void
    {
        Schema::table('candidate_sms_messages', function (Blueprint $table): void {
            $table->dropIndex('candidate_sms_recipient_source_idx');
            $table->dropConstrainedForeignId('support_group_type_id');
            $table->dropColumn('recipient_source');
        });
    }
};