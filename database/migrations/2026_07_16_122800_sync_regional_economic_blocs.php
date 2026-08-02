<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $canonicalNames = config('regional-blocs.names', []);
        $aliases = config('regional-blocs.aliases', []);

        foreach ($aliases as $from => $to) {
            $targetId = DB::table('blocs')->where('name', $to)->value('id');
            $source = DB::table('blocs')->where('name', $from)->first();

            if (! $source) {
                continue;
            }

            if ($targetId) {
                DB::table('counties')->where('bloc_id', $source->id)->update(['bloc_id' => $targetId]);

                if (DB::getSchemaBuilder()->hasColumn('candidates', 'bloc_id')) {
                    DB::table('candidates')->where('bloc_id', $source->id)->update(['bloc_id' => $targetId]);
                }

                if (DB::getSchemaBuilder()->hasColumn('polling_stations', 'bloc_id')) {
                    DB::table('polling_stations')->where('bloc_id', $source->id)->update(['bloc_id' => $targetId]);
                }
            } else {
                DB::table('blocs')->where('id', $source->id)->update([
                    'name' => $to,
                    'updated_at' => $now,
                ]);
            }
        }

        foreach ($canonicalNames as $name) {
            $existing = DB::table('blocs')->where('name', $name)->first();

            if ($existing) {
                DB::table('blocs')->where('id', $existing->id)->update([
                    'tribes' => null,
                    'voting_patterns' => null,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('blocs')->insert([
                    'name' => $name,
                    'tribes' => null,
                    'voting_patterns' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Keep regional data intact. Rolling this migration back should not
        // delete user-managed counties, candidates, or bloc assignments.
    }
};
