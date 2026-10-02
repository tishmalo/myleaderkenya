<?php

namespace Database\Seeders;

use App\Models\Bloc;
use App\Models\Candidate;
use App\Models\Constituency;
use App\Models\County;
use App\Models\PoliticalParty;
use App\Models\Position;
use App\Models\ResourceLink;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Database\Seeder;

/**
 * Demo data for reviewing the Pages & Links feature locally.
 *
 * Run with:  php artisan db:seed --class=ResourceLinkDemoSeeder --force
 */
class ResourceLinkDemoSeeder extends Seeder
{
    public function run(): void
    {
        $county = County::firstOrCreate(
            ['name' => 'Nyandarua'],
            ['bloc_id' => Bloc::firstOrCreate(['name' => 'Central'], ['tribes' => []])->id]
        );

        $mathiEast = Constituency::firstOrCreate(['name' => 'Mathi East'], ['county_id' => $county->id]);
        $mathiWest = Constituency::firstOrCreate(['name' => 'Mathi West'], ['county_id' => $county->id]);
        $kaguru = Ward::firstOrCreate(['name' => 'Kaguru'], ['constituency_id' => $mathiEast->id]);
        $mugumo = Ward::firstOrCreate(['name' => 'Mugumo'], ['constituency_id' => $mathiWest->id]);
        $manaichi = Ward::firstOrCreate(['name' => 'Manaichi'], ['constituency_id' => $mathiEast->id]);

        $mp = Position::firstOrCreate(['name' => 'Member of Parliament'], ['description' => 'MP']);
        $governor = Position::firstOrCreate(['name' => 'Governor'], ['description' => 'Governor']);
        $party = PoliticalParty::query()->whereNotNull('slug')->first()
            ?? PoliticalParty::query()->first();

        $aspirants = [
            ['name' => 'Alice Wanjiru', 'nick_name' => 'Alicia', 'position_id' => $mp->id, 'constituency' => $mathiEast->name, 'ward' => $kaguru->name],
            ['name' => 'Brian Kamau', 'nick_name' => 'BK', 'position_id' => $mp->id, 'constituency' => $mathiWest->name, 'ward' => $mugumo->name],
            ['name' => 'Carol Achieng', 'nick_name' => null, 'position_id' => $governor->id, 'constituency' => null, 'ward' => null],
            ['name' => 'Daniel Mwangi', 'nick_name' => 'Dan', 'position_id' => $mp->id, 'constituency' => $mathiEast->name, 'ward' => $manaichi->name],
        ];

        $created = [];
        foreach ($aspirants as $aspirant) {
            $created[] = Candidate::updateOrCreate(
                ['name' => $aspirant['name']],
                $aspirant + [
                    'county' => $county->name,
                    'country' => 'Kenya',
                    'approval_status' => 'approved',
                    'political_party_id' => $party?->id,
                ]
            );
        }

        $user = User::query()->where('role', 'superadmin')->first()
            ?? User::query()->first();

        $links = [
            [
                'platform' => 'facebook_group',
                'title' => 'Nyandarua County Announcements',
                'url' => 'https://www.facebook.com/share/g/1UqwN8Sw26/',
                'followers' => 12500,
                'comment' => 'Main Nyandarua county announcement group.',
                'approval_status' => ResourceLink::STATUS_APPROVED,
            ],
            [
                'platform' => 'whatsapp_group',
                'title' => 'Mathi East Voters Forum',
                'url' => 'https://chat.whatsapp.com/DEMOcounty1',
                'followers' => 4300,
                'comment' => 'Mathi East voters forum.',
                'constituency_id' => $mathiEast->id,
                'approval_status' => ResourceLink::STATUS_APPROVED,
            ],
            [
                'platform' => 'whatsapp_group',
                'title' => 'Kaguru Ward Updates',
                'url' => 'https://chat.whatsapp.com/DEMOcounty2',
                'followers' => 900,
                'comment' => 'Kaguru ward updates.',
                'ward_id' => $kaguru->id,
                'approval_status' => ResourceLink::STATUS_APPROVED,
            ],
            [
                'platform' => 'facebook_page',
                'title' => 'Manaichi Facebook page',
                'url' => 'https://www.facebook.com/profile.php?id=DEMOmanaiichi',
                'followers' => 3200,
                'comment' => 'The Manaichi community page.',
                'ward_id' => $manaichi->id,
                'approval_status' => ResourceLink::STATUS_APPROVED,
            ],
            [
                'platform' => 'x',
                'title' => 'Nyandarua Daily News',
                'url' => 'https://x.com/demoNyandarua',
                'comment' => 'Still waiting on review.',
                'approval_status' => ResourceLink::STATUS_PENDING,
            ],
        ];

        foreach ($links as $link) {
            ResourceLink::updateOrCreate(
                ['url' => $link['url']],
                array_merge($link, ['county_id' => $county->id, 'user_id' => $user?->id])
            );
        }

        $daniel = Candidate::where('name', 'Daniel Mwangi')->first();

        $this->command?->newLine();
        $this->command?->info('Demo data ready. Review these URLs:');
        $this->command?->line('  County page     /counties/'.$county->slug);
        $this->command?->line('  Aspirant (ward) /aspirants/'.$daniel?->slug);
        $this->command?->line('  Aspirant (MP)   /aspirants/'.$created[0]->slug);
        $this->command?->line('  Admin queue     /admin/links');
        $this->command?->line('  Your list       /my-account/links');
        $this->command?->newLine();
    }
}
