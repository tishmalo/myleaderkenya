<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('candidate_sms_settings')) {
            return;
        }

        Schema::table('candidate_sms_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('candidate_sms_settings', 'username')) {
                $table->text('username')->nullable()->after('base_url');
            }

            if (! Schema::hasColumn('candidate_sms_settings', 'password')) {
                $table->text('password')->nullable()->after('username');
            }
        });

        if (Schema::hasColumn('candidate_sms_settings', 'api_key')) {
            Schema::table('candidate_sms_settings', function (Blueprint $table): void {
                $table->dropColumn('api_key');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('candidate_sms_settings')) {
            return;
        }

        Schema::table('candidate_sms_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('candidate_sms_settings', 'api_key')) {
                $table->text('api_key')->nullable()->after('base_url');
            }
        });

        if (Schema::hasColumn('candidate_sms_settings', 'password')) {
            Schema::table('candidate_sms_settings', function (Blueprint $table): void {
                $table->dropColumn('password');
            });
        }

        if (Schema::hasColumn('candidate_sms_settings', 'username')) {
            Schema::table('candidate_sms_settings', function (Blueprint $table): void {
                $table->dropColumn('username');
            });
        }
    }
};
