<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('relationship');
            $table->enum('relationship', ['PA', 'campaign_manager', 'aspirant', 'voter', 'agent'])->nullable()->after('is_aspirant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'relationship')) {
                $table->dropColumn('relationship');
            }
        });
    }
};
