<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blocs', function (Blueprint $table) {
            if (! Schema::hasColumn('blocs', 'type')) {
                $table->string('type')->default('economic')->after('name')->index();
            }

            if (! Schema::hasColumn('blocs', 'description')) {
                $table->text('description')->nullable()->after('type');
            }

            if (! Schema::hasColumn('blocs', 'total_population')) {
                $table->unsignedBigInteger('total_population')->default(0)->after('description');
            }

            if (! Schema::hasColumn('blocs', 'total_registered_voters')) {
                $table->unsignedBigInteger('total_registered_voters')->default(0)->after('total_population');
            }
        });

        Schema::create('bloc_county', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bloc_id')->constrained('blocs')->cascadeOnDelete();
            $table->foreignId('county_id')->constrained('counties')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['bloc_id', 'county_id']);
            $table->index('bloc_id');
            $table->index('county_id');
        });

        DB::table('counties')
            ->whereNotNull('bloc_id')
            ->orderBy('id')
            ->get(['id', 'bloc_id'])
            ->each(function ($county) {
                DB::table('bloc_county')->updateOrInsert(
                    [
                        'bloc_id' => $county->bloc_id,
                        'county_id' => $county->id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            });

        DB::table('blocs')->orderBy('id')->get(['id'])->each(function ($bloc) {
            $totals = DB::table('counties')
                ->join('bloc_county', 'counties.id', '=', 'bloc_county.county_id')
                ->where('bloc_county.bloc_id', $bloc->id)
                ->selectRaw('COALESCE(SUM(counties.population), 0) as population')
                ->selectRaw('COALESCE(SUM(counties.registered_voters), 0) as registered_voters')
                ->first();

            DB::table('blocs')
                ->where('id', $bloc->id)
                ->update([
                    'total_population' => (int) $totals->population,
                    'total_registered_voters' => (int) $totals->registered_voters,
                ]);
        });

        Schema::table('counties', function (Blueprint $table) {
            $table->dropForeign(['bloc_id']);
        });

        Schema::table('counties', function (Blueprint $table) {
            $table->unsignedBigInteger('bloc_id')->nullable()->change();
            $table->foreign('bloc_id')->references('id')->on('blocs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('counties', function (Blueprint $table) {
            $table->dropForeign(['bloc_id']);
        });

        Schema::table('counties', function (Blueprint $table) {
            $table->unsignedBigInteger('bloc_id')->nullable(false)->change();
            $table->foreign('bloc_id')->references('id')->on('blocs')->cascadeOnDelete();
        });

        Schema::dropIfExists('bloc_county');

        Schema::table('blocs', function (Blueprint $table) {
            foreach (['total_registered_voters', 'total_population', 'description', 'type'] as $column) {
                if (Schema::hasColumn('blocs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
