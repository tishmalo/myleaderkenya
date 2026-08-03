<?php

use Illuminate\Contracts\Encryption\DecryptException;
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
        Schema::table('users', function (Blueprint $table) {
            try {
                $table->dropUnique('users_email_unique');
            } catch (\Throwable) {
            }

            if (! Schema::hasColumn('users', 'email_hash')) {
                $table->string('email_hash', 64)->nullable()->unique()->after('email');
            }

            if (! Schema::hasColumn('users', 'phone_hash')) {
                $table->string('phone_hash', 64)->nullable()->index()->after('phone');
            }

            if (! Schema::hasColumn('users', 'id_number')) {
                $table->text('id_number')->nullable()->after('phone_hash');
            }

            if (! Schema::hasColumn('users', 'id_number_hash')) {
                $table->string('id_number_hash', 64)->nullable()->unique()->after('id_number');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->text('email')->nullable()->change();

            if (Schema::hasColumn('users', 'phone')) {
                $table->text('phone')->nullable()->change();
            }

            if (Schema::hasColumn('users', 'id_number')) {
                $table->text('id_number')->nullable()->change();
            }
        });

        Schema::table('candidates', function (Blueprint $table) {
            if (! Schema::hasColumn('candidates', 'email_hash')) {
                $table->string('email_hash', 64)->nullable()->index()->after('email');
            }

            if (! Schema::hasColumn('candidates', 'phone_hash')) {
                $table->string('phone_hash', 64)->nullable()->index()->after('phone');
            }
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->text('email')->nullable()->change();
            $table->text('phone')->nullable()->change();
        });

        $this->encryptTablePii('users', [
            'email' => 'email_hash',
            'phone' => 'phone_hash',
            'id_number' => 'id_number_hash',
        ]);

        $this->encryptTablePii('candidates', [
            'email' => 'email_hash',
            'phone' => 'phone_hash',
        ]);
    }

    public function down(): void
    {
        $this->decryptTablePii('users', ['email', 'phone', 'id_number']);
        $this->decryptTablePii('candidates', ['email', 'phone']);

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'email_hash')) {
                $table->dropUnique('users_email_hash_unique');
                $table->dropColumn('email_hash');
            }

            if (Schema::hasColumn('users', 'phone_hash')) {
                $table->dropIndex('users_phone_hash_index');
                $table->dropColumn('phone_hash');
            }

            if (Schema::hasColumn('users', 'id_number_hash')) {
                $table->dropUnique('users_id_number_hash_unique');
                $table->dropColumn('id_number_hash');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();

            if (Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->change();
            }

            if (Schema::hasColumn('users', 'id_number')) {
                $table->dropColumn('id_number');
            }

            $table->unique('email');
        });

        Schema::table('candidates', function (Blueprint $table) {
            if (Schema::hasColumn('candidates', 'email_hash')) {
                $table->dropIndex('candidates_email_hash_index');
                $table->dropColumn('email_hash');
            }

            if (Schema::hasColumn('candidates', 'phone_hash')) {
                $table->dropIndex('candidates_phone_hash_index');
                $table->dropColumn('phone_hash');
            }
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('phone')->nullable()->change();
        });
    }

    private function encryptTablePii(string $table, array $columns): void
    {
        DB::table($table)
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($table, $columns) {
                foreach ($rows as $row) {
                    $updates = [];

                    foreach ($columns as $column => $hashColumn) {
                        if (! property_exists($row, $column)) {
                            continue;
                        }

                        $plain = $this->decryptIfEncrypted($row->{$column});
                        $updates[$column] = $plain === null ? null : Crypt::encryptString($plain);

                        if (Schema::hasColumn($table, $hashColumn)) {
                            $updates[$hashColumn] = $this->piiHash($plain);
                        }
                    }

                    if ($updates !== []) {
                        DB::table($table)->where('id', $row->id)->update($updates);
                    }
                }
            });
    }

    private function decryptTablePii(string $table, array $columns): void
    {
        DB::table($table)
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($table, $columns) {
                foreach ($rows as $row) {
                    $updates = [];

                    foreach ($columns as $column) {
                        if (! property_exists($row, $column)) {
                            continue;
                        }

                        $updates[$column] = $this->decryptIfEncrypted($row->{$column});
                    }

                    if ($updates !== []) {
                        DB::table($table)->where('id', $row->id)->update($updates);
                    }
                }
            });
    }

    private function decryptIfEncrypted($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString((string) $value);
        } catch (DecryptException) {
            return (string) $value;
        }
    }

    private function piiHash($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return hash_hmac('sha256', Str::lower(trim((string) $value)), (string) config('app.key'));
    }
};
