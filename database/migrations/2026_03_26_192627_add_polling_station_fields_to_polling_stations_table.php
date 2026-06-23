<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('polling_stations', function (Blueprint $table) {
            
            // Only add column if it doesn't already exist
            if (!Schema::hasColumn('polling_stations', 'county')) {
                $table->string('county')->after('id');
            }

            if (!Schema::hasColumn('polling_stations', 'constituency')) {
                $table->string('constituency')->after('county');
            }

            if (!Schema::hasColumn('polling_stations', 'office')) {
                $table->string('office')->after('constituency');
            }

            if (!Schema::hasColumn('polling_stations', 'near_landmark')) {
                $table->string('near_landmark')->nullable()->after('office');
            }

            if (!Schema::hasColumn('polling_stations', 'distance_to_office')) {
                $table->integer('distance_to_office')->default(0)->after('near_landmark');
            }

            if (!Schema::hasColumn('polling_stations', 'lat')) {
                $table->decimal('lat', 10, 8)->after('distance_to_office');
            }

            if (!Schema::hasColumn('polling_stations', 'lon')) {
                $table->decimal('lon', 11, 8)->after('lat');
            }

            if (!Schema::hasColumn('polling_stations', 'is_user_added')) {
                $table->boolean('is_user_added')->default(false)->after('lon');
            }

            // Add index for nearby queries (only if it doesn't exist)
            if (!Schema::hasIndex('polling_stations', ['lat', 'lon'])) {
                $table->index(['lat', 'lon']);
            }
        });
    }

    public function down()
    {
        Schema::table('polling_stations', function (Blueprint $table) {
            $table->dropColumn([
                'county', 
                'constituency', 
                'office', 
                'near_landmark',
                'distance_to_office', 
                'lat', 
                'lon', 
                'is_user_added'
            ]);
        });
    }
};