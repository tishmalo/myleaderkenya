<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->foreignId('political_party_id')
                ->nullable()
                ->after('position_id')
                ->constrained('political_parties')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropForeign(['political_party_id']);
            $table->dropColumn('political_party_id');
        });
    }
};