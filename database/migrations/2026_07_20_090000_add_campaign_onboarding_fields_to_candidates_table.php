<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table): void {
            if (! Schema::hasColumn('candidates', 'campaign_poster')) {
                $table->string('campaign_poster')->nullable()->after('cover_photo');
            }

            if (! Schema::hasColumn('candidates', 'campaign_video')) {
                $table->string('campaign_video')->nullable()->after('campaign_poster');
            }

            if (! Schema::hasColumn('candidates', 'campaign_skiza_audio')) {
                $table->string('campaign_skiza_audio')->nullable()->after('campaign_video');
            }

            if (! Schema::hasColumn('candidates', 'phone_1')) {
                $table->string('phone_1')->nullable()->after('phone');
            }

            if (! Schema::hasColumn('candidates', 'phone_2')) {
                $table->string('phone_2')->nullable()->after('phone_1');
            }

            if (! Schema::hasColumn('candidates', 'email_1')) {
                $table->string('email_1')->nullable()->after('email');
            }

            if (! Schema::hasColumn('candidates', 'email_2')) {
                $table->string('email_2')->nullable()->after('email_1');
            }
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table): void {
            foreach (['campaign_poster', 'campaign_video', 'campaign_skiza_audio', 'phone_1', 'phone_2', 'email_1', 'email_2'] as $column) {
                if (Schema::hasColumn('candidates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};