<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'gender')) {
                $table->string('gender')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'year_of_birth')) {
                $table->integer('year_of_birth')->nullable()->after('gender');
            }
            if (!Schema::hasColumn('users', 'county')) {
                $table->string('county')->nullable()->after('year_of_birth');
            }
            if (!Schema::hasColumn('users', 'constituency')) {
                $table->string('constituency')->nullable()->after('county');
            }
            if (!Schema::hasColumn('users', 'ward')) {
                $table->string('ward')->nullable()->after('constituency');
            }
            if (!Schema::hasColumn('users', 'polling_station')) {
                $table->string('polling_station')->nullable()->after('ward');
            }
            if (!Schema::hasColumn('users', 'country_of_residence')) {
                $table->string('country_of_residence')->nullable()->after('polling_station');
            }
            if (!Schema::hasColumn('users', 'is_voter')) {
                $table->boolean('is_voter')->default(false)->after('country_of_residence');
            }
            if (!Schema::hasColumn('users', 'is_registered')) {
                $table->boolean('is_registered')->default(false)->after('is_voter');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'gender', 'year_of_birth', 'county', 'constituency',
                'ward', 'polling_station', 'country_of_residence', 
                'is_voter', 'is_registered'
            ]);
        });
    }
};