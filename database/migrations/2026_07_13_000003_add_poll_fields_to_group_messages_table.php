<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_messages', function (Blueprint $table) {
            $table->string('message_type')->default('text')->after('message');
            $table->foreignId('aspirant_poll_id')->nullable()->after('message_type')->constrained('aspirant_polls')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('group_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('aspirant_poll_id');
            $table->dropColumn('message_type');
        });
    }
};
