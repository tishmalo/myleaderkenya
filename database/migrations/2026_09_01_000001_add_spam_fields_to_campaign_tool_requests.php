<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('campaign_tool_requests')) {
            return;
        }

        Schema::table('campaign_tool_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('campaign_tool_requests', 'is_spam')) {
                $table->boolean('is_spam')->default(false)->after('status');
            }

            if (! Schema::hasColumn('campaign_tool_requests', 'spam_reason')) {
                $table->string('spam_reason')->nullable()->after('is_spam');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('campaign_tool_requests')) {
            return;
        }

        Schema::table('campaign_tool_requests', function (Blueprint $table): void {
            foreach (['spam_reason', 'is_spam'] as $column) {
                if (Schema::hasColumn('campaign_tool_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
