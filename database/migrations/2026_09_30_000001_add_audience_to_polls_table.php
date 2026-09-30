<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A poll's audience is derived from the aspirants it is built from. Words
     * polls and presidential polls are national (guests may view them); every
     * other poll is scoped to the county, constituency or ward its aspirants
     * share. `audience_scope` says which rule applies and the three columns
     * carry the matching region, if any.
     */
    public function up(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->string('audience_scope')->default('national')->after('status');
            $table->string('audience_county')->nullable()->after('audience_scope');
            $table->string('audience_constituency')->nullable()->after('audience_county');
            $table->string('audience_ward')->nullable()->after('audience_constituency');

            // The homepage asks for all open polls, then filters by audience.
            $table->index(['status', 'ends_at', 'audience_scope'], 'polls_status_audience_idx');
        });
    }

    public function down(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->dropIndex('polls_status_audience_idx');
            $table->dropColumn(['audience_scope', 'audience_county', 'audience_constituency', 'audience_ward']);
        });
    }
};
