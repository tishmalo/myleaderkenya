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
        if (! Schema::hasColumn('counties', 'slug')) {
            Schema::table('counties', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });
        }

        DB::table('counties')->select('id', 'name', 'slug')->orderBy('id')->chunkById(200, function ($counties) {
            foreach ($counties as $county) {
                if ($county->slug) {
                    continue;
                }

                $base = Str::slug($county->name) ?: 'county';
                $slug = $base;
                $suffix = 2;

                while (DB::table('counties')->where('slug', $slug)->where('id', '!=', $county->id)->exists()) {
                    $slug = $base.'-'.$suffix++;
                }

                DB::table('counties')->where('id', $county->id)->update(['slug' => $slug]);
            }
        });

        Schema::table('counties', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('counties', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
