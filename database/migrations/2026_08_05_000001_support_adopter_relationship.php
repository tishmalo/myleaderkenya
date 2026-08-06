<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'relationship')) {
            DB::statement('ALTER TABLE users MODIFY relationship VARCHAR(50) NULL');
        }
        if (Schema::hasTable('candidate_user_relationships') && Schema::hasColumn('candidate_user_relationships', 'relationship')) {
            DB::statement('ALTER TABLE candidate_user_relationships MODIFY relationship VARCHAR(50) NULL');
        }
        if (Schema::hasTable('candidate_claim_requests') && Schema::hasColumn('candidate_claim_requests', 'relationship')) {
            DB::statement('ALTER TABLE candidate_claim_requests MODIFY relationship VARCHAR(50) NOT NULL');
        }
    }

    public function down(): void
    {
        // Keep relationship columns extensible and preserve historical adopter records.
    }
};