<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('county')->after('longitude');
            $table->string('constituency')->after('county');
            
            // Index for fast chatroom queries
            $table->index(['county', 'constituency']);
        });
    }

    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['county', 'constituency']);
        });
    }
};