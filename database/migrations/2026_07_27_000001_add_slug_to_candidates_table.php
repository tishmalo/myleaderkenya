<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('candidates', 'slug')) {
            Schema::table('candidates', function (Blueprint $table): void {
                $table->string('slug')->nullable()->unique()->after('name');
            });
        }

        $usedSlugs = [];

        DB::table('candidates')
            ->select('id', 'name', 'slug')
            ->orderBy('id')
            ->chunkById(100, function ($candidates) use (&$usedSlugs): void {
                foreach ($candidates as $candidate) {
                    $existingSlug = trim((string) ($candidate->slug ?? ''));
                    $baseSlug = Str::slug($existingSlug !== '' ? $existingSlug : $candidate->name);
                    $baseSlug = $baseSlug !== '' ? $baseSlug : 'aspirant-' . $candidate->id;
                    $slug = $baseSlug;
                    $suffix = 2;

                    while (isset($usedSlugs[$slug])) {
                        $slug = $baseSlug . '-' . $suffix++;
                    }

                    $usedSlugs[$slug] = true;

                    if ($slug !== $existingSlug) {
                        DB::table('candidates')
                            ->where('id', $candidate->id)
                            ->update(['slug' => $slug]);
                    }
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('candidates', 'slug')) {
            Schema::table('candidates', function (Blueprint $table): void {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            });
        }
    }
};
