<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'approval_status')) {
                $table->string('approval_status')->default('approved')->after('is_active')->index();
            }

            if (! Schema::hasColumn('events', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('events', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('events', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            foreach (['reviewed_at', 'reviewed_by', 'created_by'] as $column) {
                if (Schema::hasColumn('events', $column)) {
                    if (in_array($column, ['reviewed_by', 'created_by'], true)) {
                        $table->dropConstrainedForeignId($column);
                    } else {
                        $table->dropColumn($column);
                    }
                }
            }

            if (Schema::hasColumn('events', 'approval_status')) {
                $table->dropColumn('approval_status');
            }
        });
    }
};
