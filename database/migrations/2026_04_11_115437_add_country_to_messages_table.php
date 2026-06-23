<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'country')) {
                $table->id('country')->nullable()->after('longitude');
            }

            if (!Schema::hasColumn('messages', 'country')) {
                $table->foreignId('country')
                      ->nullable()
                      ->after('id')
                      ->constrained('countries')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'country')) {
                $table->dropForeign(['country']);
                $table->dropColumn('country');
            }

          
        });
    }
};