<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('counties', function (Blueprint $table) {
            if (! Schema::hasColumn('counties', 'image')) {
                $table->string('image')->nullable()->after('postal_abbreviation');
            }
        });

        Schema::table('constituencies', function (Blueprint $table) {
            if (! Schema::hasColumn('constituencies', 'image')) {
                $table->string('image')->nullable()->after('position_name');
            }
        });

        Schema::table('wards', function (Blueprint $table) {
            if (! Schema::hasColumn('wards', 'image')) {
                $table->string('image')->nullable()->after('registered_voters');
            }
        });
    }

    public function down(): void
    {
        Schema::table('wards', function (Blueprint $table) {
            if (Schema::hasColumn('wards', 'image')) {
                $table->dropColumn('image');
            }
        });

        Schema::table('constituencies', function (Blueprint $table) {
            if (Schema::hasColumn('constituencies', 'image')) {
                $table->dropColumn('image');
            }
        });

        Schema::table('counties', function (Blueprint $table) {
            if (Schema::hasColumn('counties', 'image')) {
                $table->dropColumn('image');
            }
        });
    }
};
