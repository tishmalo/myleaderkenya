<?php

namespace Database\Seeders;

use App\Models\SeoPage;
use Illuminate\Database\Seeder;

class SeoPageSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->pages() as $page) {
            SeoPage::query()->updateOrCreate(
                ['route_name' => $page['route_name']],
                $page
            );
        }
    }

    /**
     * Metadata for public pages whose SEO used to be hardcoded in controllers.
     *
     * @return list<array<string, string|null>>
     */
    private function pages(): array
    {
        return [
            [
                'route_name' => 'campaign-tools.public',
                'title' => 'Kenya Election Campaign Software Tools | My Leader Kenya',
                'description' => 'Powerful campaign tools for Kenya 2027 aspirants: bulk SMS, opinion polls, call center, website builder, support groups and more. Available on My Leader Kenya.',
                // A blank canonical resolves to the page's own URL when rendered.
                'canonical' => null,
            ],
        ];
    }
}
