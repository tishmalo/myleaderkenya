<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('polling_stations', function (Blueprint $table) {
        $table->foreignId('bloc_id')->nullable()->constrained('blocs')->after('id');
    });
}

public function down()
{
    Schema::table('polling_stations', function (Blueprint $table) {
        $table->dropForeign(['bloc_id']);
        $table->dropColumn('bloc_id');
    });
}
};
