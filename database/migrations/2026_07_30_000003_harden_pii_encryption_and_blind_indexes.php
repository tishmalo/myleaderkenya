<?php

use App\Support\PiiProtection;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->protectTable('users', ['email', 'phone'], 'email', 'email_hash');
        $this->protectTable('candidate_claim_requests', ['email', 'phone'], 'email', 'email_hash');
        $this->protectTable('candidates', ['email', 'phone', 'email_1', 'email_2', 'phone_1', 'phone_2']);
    }

    public function down(): void
    {
        // Encryption and keyed blind indexes must never be downgraded.
    }

    private function protectTable(
        string $table,
        array $piiColumns,
        ?string $emailColumn = null,
        ?string $blindIndexColumn = null
    ): void {
        if (! Schema::hasTable($table)) {
            return;
        }

        $columns = array_values(array_filter(
            $piiColumns,
            fn (string $column): bool => Schema::hasColumn($table, $column)
        ));

        if ($blindIndexColumn && Schema::hasColumn($table, $blindIndexColumn)) {
            $columns[] = $blindIndexColumn;
        }

        DB::table($table)
            ->select(array_values(array_unique(array_merge(['id'], $columns))))
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($table, $piiColumns, $emailColumn, $blindIndexColumn): void {
                foreach ($rows as $row) {
                    $updates = [];
                    $plaintext = [];

                    foreach ($piiColumns as $column) {
                        if (! property_exists($row, $column) || blank($row->{$column})) {
                            continue;
                        }

                        $value = (string) $row->{$column};
                        $decrypted = $this->decryptExisting($value);

                        if ($decrypted === null) {
                            continue;
                        }

                        $plaintext[$column] = $decrypted;

                        if (! $this->looksEncrypted($value)) {
                            $updates[$column] = Crypt::encryptString($decrypted);
                        }
                    }

                    if (
                        $emailColumn
                        && $blindIndexColumn
                        && array_key_exists($emailColumn, $plaintext)
                    ) {
                        $updates[$blindIndexColumn] = PiiProtection::emailBlindIndex($plaintext[$emailColumn]);
                    }

                    if ($updates !== []) {
                        DB::table($table)->where('id', $row->id)->update($updates);
                    }
                }
            });
    }

    private function decryptExisting(string $value): ?string
    {
        if (! $this->looksEncrypted($value)) {
            return trim($value);
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            // Corrupt ciphertext is left untouched and is never treated as plaintext.
            return null;
        }
    }

    private function looksEncrypted(string $value): bool
    {
        $payload = json_decode((string) base64_decode($value, true), true);

        return is_array($payload)
            && isset($payload['iv'], $payload['value'], $payload['mac']);
    }
};
