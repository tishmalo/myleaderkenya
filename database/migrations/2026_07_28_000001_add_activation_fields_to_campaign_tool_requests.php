<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('campaign_tool_requests')) {
            return;
        }

        if (Schema::hasColumn('campaign_tool_requests', 'campaign_tool_id')) {
            DB::statement('ALTER TABLE campaign_tool_requests MODIFY campaign_tool_id BIGINT UNSIGNED NULL');
        }

        Schema::table('campaign_tool_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('campaign_tool_requests', 'request_type')) {
                $table->string('request_type')->default('feature')->after('candidate_id');
            }

            if (! Schema::hasColumn('campaign_tool_requests', 'tool_key')) {
                $table->string('tool_key')->nullable()->after('request_type');
            }

            if (! Schema::hasColumn('campaign_tool_requests', 'tool_title')) {
                $table->string('tool_title')->nullable()->after('tool_key');
            }

            if (! Schema::hasColumn('campaign_tool_requests', 'disabled_reason')) {
                $table->text('disabled_reason')->nullable()->after('use_case');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('campaign_tool_requests')) {
            return;
        }

        Schema::table('campaign_tool_requests', function (Blueprint $table): void {
            foreach (['disabled_reason', 'tool_title', 'tool_key', 'request_type'] as $column) {
                if (Schema::hasColumn('campaign_tool_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
