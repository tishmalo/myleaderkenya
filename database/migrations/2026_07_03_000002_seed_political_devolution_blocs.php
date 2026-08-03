<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $blocs = [
        'Lake Region (LREB)' => [
            'Kisumu',
            'Siaya',
            'Homa Bay',
            'Migori',
            'Kisii',
            'Nyamira',
            'Kakamega',
            'Vihiga',
            'Bungoma',
            'Busia',
            'Bomet',
            'Kericho',
            'Nandi',
            'Trans Nzoia',
        ],
        'North Rift (NOREB)' => [
            'Uasin Gishu',
            'Elgeyo Marakwet',
            'West Pokot',
            'Turkana',
            'Samburu',
        ],
        'Mt. Kenya' => [
            'Kiambu',
            "Murang'a",
            'Nyeri',
            'Kirinyaga',
            'Nyandarua',
            'Meru',
            'Tharaka-Nithi',
            'Embu',
            'Laikipia',
        ],
        'South Eastern' => [
            'Machakos',
            'Kitui',
            'Makueni',
            'Taita-Taveta',
        ],
        'Coast (Pwani)' => [
            'Mombasa',
            'Kwale',
            'Kilifi',
            'Tana River',
            'Lamu',
        ],
        'Frontier (FCDC)' => [
            'Garissa',
            'Wajir',
            'Mandera',
            'Marsabit',
            'Isiolo',
        ],
        'Nairobi Metro' => [
            'Nairobi',
        ],
    ];

    public function up(): void
    {
        foreach ($this->blocs as $blocName => $countyNames) {
            DB::table('blocs')->updateOrInsert(
                ['name' => $blocName],
                [
                    'type' => 'political',
                    'description' => $this->description($blocName),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $blocId = DB::table('blocs')->where('name', $blocName)->value('id');
            $countyIds = DB::table('counties')
                ->whereIn('name', $countyNames)
                ->pluck('id', 'name');

            foreach ($countyNames as $countyName) {
                $countyId = $countyIds[$countyName] ?? null;
                if (! $countyId) {
                    continue;
                }

                DB::table('bloc_county')->updateOrInsert(
                    [
                        'bloc_id' => $blocId,
                        'county_id' => $countyId,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            $this->recalculateTotals($blocId);
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->blocs) as $blocName) {
            $blocId = DB::table('blocs')->where('name', $blocName)->value('id');
            if (! $blocId) {
                continue;
            }

            DB::table('bloc_county')->where('bloc_id', $blocId)->delete();
            DB::table('counties')->where('bloc_id', $blocId)->update(['bloc_id' => null]);
            DB::table('blocs')->where('id', $blocId)->delete();
        }
    }

    private function recalculateTotals(int $blocId): void
    {
        $totals = DB::table('counties')
            ->join('bloc_county', 'counties.id', '=', 'bloc_county.county_id')
            ->where('bloc_county.bloc_id', $blocId)
            ->selectRaw('COALESCE(SUM(counties.population), 0) as population')
            ->selectRaw('COALESCE(SUM(counties.registered_voters), 0) as registered_voters')
            ->first();

        DB::table('blocs')
            ->where('id', $blocId)
            ->update([
                'total_population' => (int) $totals->population,
                'total_registered_voters' => (int) $totals->registered_voters,
            ]);
    }

    private function description(string $blocName): string
    {
        return "Political devolution bloc grouping for {$blocName}.";
    }
};
