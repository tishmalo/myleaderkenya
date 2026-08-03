<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('political_parties', function (Blueprint $table) {
            $table->string('phone_1', 50)->nullable()->after('website_url');
            $table->string('phone_2', 50)->nullable()->after('phone_1');
            $table->string('email_1')->nullable()->after('phone_2');
            $table->string('email_2')->nullable()->after('email_1');
        });
    }

    public function down(): void
    {
        Schema::table('political_parties', function (Blueprint $table) {
            $table->dropColumn(['phone_1', 'phone_2', 'email_1', 'email_2']);
        });
    }
};
