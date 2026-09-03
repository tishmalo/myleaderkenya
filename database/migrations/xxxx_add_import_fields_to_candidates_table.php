<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->boolean('is_imported')->default(false)->after('approval_status');
            $table->string('import_status', 20)->nullable()->after('is_imported'); // pending|published|discarded
            $table->foreignId('linked_candidate_id')
                ->nullable()
                ->after('import_status')
                ->constrained('candidates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('linked_candidate_id');
            $table->dropColumn(['is_imported', 'import_status']);
        });
    }
};