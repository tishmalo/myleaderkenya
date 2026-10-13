<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Presidential aspirants have no county, so a link for one could not be saved:
 * county_id was NOT NULL and the form demanded a county, which also pushed the
 * link onto some county's page it has nothing to do with.
 *
 * A link with no county is a national link: it may carry a presidential
 * aspirant and it shows on the national aspirants page (aspirants?position=1)
 * and on that aspirant's profile, never on a county page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resource_links', function (Blueprint $table): void {
            $table->foreignId('county_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // National links cannot exist under the restored NOT NULL constraint, so
        // the rollback drops them rather than failing halfway through.
        DB::table('resource_links')->whereNull('county_id')->delete();

        Schema::table('resource_links', function (Blueprint $table): void {
            $table->foreignId('county_id')->nullable(false)->change();
        });
    }
};
