<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_aspirant')->default(false)->after('is_registered');
            
            // For password reset
            $table->string('otp')->nullable()->after('is_aspirant');
            $table->timestamp('otp_expires_at')->nullable()->after('otp');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_aspirant', 'otp', 'otp_expires_at']);
        });
    }
};