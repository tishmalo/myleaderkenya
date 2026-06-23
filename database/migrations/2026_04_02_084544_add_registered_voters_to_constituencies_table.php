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
        Schema::table('constituencies', function (Blueprint $table) {
            $table->unsignedBigInteger('registered_voters')->default(0)->after('population');
        });
    }

    public function down()
    {
        Schema::table('constituencies', function (Blueprint $table) {
            $table->dropColumn('registered_voters');
        });
    }
};
