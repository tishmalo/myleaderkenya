<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->text('latitude')->nullable()->change();
            $table->text('longitude')->nullable()->change();
        });

        DB::table('locations')->orderBy('id')->chunkById(100, function ($locations) {
            foreach ($locations as $location) {
                $updates = [];

                foreach (['latitude', 'longitude'] as $column) {
                    $value = $location->{$column};

                    if ($value === null || $value === '') {
                        continue;
                    }

                    try {
                        Crypt::decryptString($value);
                    } catch (DecryptException) {
                        $updates[$column] = Crypt::encryptString((string) $value);
                    }
                }

                if ($updates !== []) {
                    DB::table('locations')->where('id', $location->id)->update($updates);
                }
            }
        });
    }

    public function down(): void
    {
        DB::table('locations')->orderBy('id')->chunkById(100, function ($locations) {
            foreach ($locations as $location) {
                $updates = [];

                foreach (['latitude', 'longitude'] as $column) {
                    $value = $location->{$column};

                    if ($value === null || $value === '') {
                        continue;
                    }

                    try {
                        $updates[$column] = Crypt::decryptString($value);
                    } catch (DecryptException) {
                        $updates[$column] = $value;
                    }
                }

                if ($updates !== []) {
                    DB::table('locations')->where('id', $location->id)->update($updates);
                }
            }
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->change();
            $table->decimal('longitude', 11, 8)->nullable()->change();
        });
    }
};
