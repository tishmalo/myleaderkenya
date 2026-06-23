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
        Schema::table('polling_stations', function (Blueprint $table) {
    $table->string('ward')->nullable()->after('constituency');
    $table->unsignedInteger('registered_voters')->default(0)->after('ward');
    $table->foreignId('bloc_id')->nullable()->constrained()->after('id');
});
        // Schema::table('polling_stations', function (Blueprint $table) {
        //     //
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('polling_stations', function (Blueprint $table) {
            //
        });
    }
};
