<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('candidate_user_relationships')) {
            return;
        }

        Schema::table('candidate_user_relationships', function (Blueprint $table): void {
            if (! Schema::hasColumn('candidate_user_relationships', 'dashboard_access_enabled')) {
                $table->boolean('dashboard_access_enabled')->default(true)->after('relationship');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('candidate_user_relationships')) {
            return;
        }

        Schema::table('candidate_user_relationships', function (Blueprint $table): void {
            if (Schema::hasColumn('candidate_user_relationships', 'dashboard_access_enabled')) {
                $table->dropColumn('dashboard_access_enabled');
            }
        });
    }
};
