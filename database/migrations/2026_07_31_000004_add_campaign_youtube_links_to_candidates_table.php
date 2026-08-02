<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table): void {
            $table->string('campaign_video_url')->nullable()->after('campaign_video');
            $table->string('campaign_song_url')->nullable()->after('campaign_video_url');
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table): void {
            $table->dropColumn(['campaign_video_url', 'campaign_song_url']);
        });
    }
};