<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->foreignId('bloc_id')
                  ->nullable()
                  ->constrained('blocs')
                  ->onDelete('set null')
                  ->after('position_id');
        });
    }

    public function down()
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropForeign(['bloc_id']);
            $table->dropColumn('bloc_id');
        });
    }
};