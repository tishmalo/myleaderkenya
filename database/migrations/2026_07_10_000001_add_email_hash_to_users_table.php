<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'email_hash')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('email_hash', 64)->nullable()->after('email')->index();
            });
        }

        DB::table('users')
            ->whereNotNull('email')
            ->where(function ($query) {
                $query->whereNull('email_hash')->orWhere('email_hash', '');
            })
            ->orderBy('id')
            ->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    $email = $this->plainEmail($user->email);

                    if ($email === null || $email === '') {
                        continue;
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['email_hash' => hash('sha256', Str::lower(trim($email)))]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'email_hash')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('email_hash');
            });
        }
    }

    private function plainEmail(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable) {
            return $value;
        }
    }
};