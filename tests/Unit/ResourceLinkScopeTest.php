<?php

namespace Tests\Unit;

use App\Models\Candidate;
use App\Models\Position;
use App\Models\ResourceLink;
use App\Support\ResourceLinkScope;
use Tests\TestCase;

/**
 * The scope rules that decide whether a link belongs to a county or to the
 * whole country. No database needed: the helpers are pure.
 */
class ResourceLinkScopeTest extends TestCase
{
    private function candidate(string $positionName): Candidate
    {
        $candidate = new Candidate();
        $candidate->setRelation('position', new Position(['name' => $positionName]));

        return $candidate;
    }

    public function test_only_presidential_candidates_are_presidential(): void
    {
        $this->assertTrue($this->candidate('President')->isPresidential());
        $this->assertTrue($this->candidate('presidential')->isPresidential());
        $this->assertTrue($this->candidate(' President ')->isPresidential());

        $this->assertFalse($this->candidate('Governor')->isPresidential());
        $this->assertFalse($this->candidate('Member of Parliament')->isPresidential());
        $this->assertFalse($this->candidate('')->isPresidential());
        $this->assertFalse((new Candidate())->isPresidential());
    }

    public function test_the_national_flag_or_a_presidential_aspirant_makes_a_link_national(): void
    {
        $this->assertTrue(ResourceLinkScope::isNational(['is_national' => '1']));
        $this->assertTrue(ResourceLinkScope::isNational(['is_national' => true]));
        $this->assertTrue(ResourceLinkScope::isNational([], $this->candidate('President')));

        $this->assertFalse(ResourceLinkScope::isNational([]));
        $this->assertFalse(ResourceLinkScope::isNational(['is_national' => '0']));
        $this->assertFalse(ResourceLinkScope::isNational([], $this->candidate('Governor')));
    }

    public function test_a_national_link_loses_its_geographic_fields(): void
    {
        $data = ResourceLinkScope::apply([
            'platform' => 'facebook_group',
            'title' => 'Kenya Youth Forum',
            'county_id' => 12,
            'constituency_id' => 40,
            'ward_id' => 90,
            'is_national' => '1',
        ]);

        $this->assertNull($data['county_id']);
        $this->assertNull($data['constituency_id']);
        $this->assertNull($data['ward_id']);
        $this->assertArrayNotHasKey('is_national', $data);
        $this->assertSame('Kenya Youth Forum', $data['title']);
    }

    public function test_a_presidential_aspirant_forces_a_national_link(): void
    {
        $data = ResourceLinkScope::apply([
            'platform' => 'facebook_page',
            'title' => 'Presidential page',
            'county_id' => 12,
        ], $this->candidate('President'));

        $this->assertNull($data['county_id']);
    }

    public function test_a_county_link_keeps_its_geography(): void
    {
        $data = ResourceLinkScope::apply([
            'platform' => 'facebook_group',
            'title' => 'Bungoma group',
            'county_id' => 12,
            'constituency_id' => 40,
        ], $this->candidate('Governor'));

        $this->assertSame(12, $data['county_id']);
        $this->assertSame(40, $data['constituency_id']);
    }

    public function test_the_model_reports_a_national_link(): void
    {
        $national = new ResourceLink(['platform' => 'facebook_group', 'county_id' => null]);
        $county = new ResourceLink(['platform' => 'facebook_group', 'county_id' => 7]);

        $this->assertTrue($national->isNational());
        $this->assertFalse($county->isNational());
    }
}
