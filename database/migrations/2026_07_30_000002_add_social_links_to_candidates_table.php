<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table): void {
            foreach (['facebook_url', 'x_url', 'instagram_url', 'tiktok_url', 'youtube_url'] as $column) {
                if (! Schema::hasColumn('candidates', $column)) {
                    $table->string($column)->nullable()->after('about');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table): void {
            foreach (['facebook_url', 'x_url', 'instagram_url', 'tiktok_url', 'youtube_url'] as $column) {
                if (Schema::hasColumn('candidates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
