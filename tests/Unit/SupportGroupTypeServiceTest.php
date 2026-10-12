<?php

namespace Tests\Unit;

use App\Contracts\Repositories\Admin\SupportGroupTypeRepositoryInterface;
use App\Models\SupportGroupType;
use App\Services\Admin\SupportGroupTypeService;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class SupportGroupTypeServiceTest extends TestCase
{
    private function service(?SupportGroupTypeRepositoryInterface $types = null): SupportGroupTypeService
    {
        return new SupportGroupTypeService(
            $types ?? $this->createMock(SupportGroupTypeRepositoryInterface::class),
        );
    }

    public function test_list_returns_types_with_contact_counts(): void
    {
        $types = new Collection([
            new SupportGroupType(['name' => 'Friends', 'slug' => 'friends']),
        ]);

        $repository = $this->createMock(SupportGroupTypeRepositoryInterface::class);
        $repository->expects($this->once())->method('allWithContactCountOrdered')->willReturn($types);

        $this->assertSame($types, $this->service($repository)->list());
    }

    public function test_create_generates_a_slug_and_defaults_sort_order(): void
    {
        $captured = null;
        $repository = $this->createMock(SupportGroupTypeRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('create')
            ->willReturnCallback(function (array $attributes) use (&$captured) {
                $captured = $attributes;

                return new SupportGroupType($attributes);
            });

        $result = $this->service($repository)->create(['name' => 'Volunteer Network'], true);

        $this->assertSame('success', $result['status']);
        $this->assertSame('Support group type added.', $result['message']);
        $this->assertSame('volunteer-network', $captured['slug']);
        $this->assertSame(0, $captured['sort_order']);
        $this->assertTrue($captured['is_active']);
    }

    public function test_create_respects_provided_sort_order_and_activity(): void
    {
        $captured = null;
        $repository = $this->createMock(SupportGroupTypeRepositoryInterface::class);
        $repository->method('create')->willReturnCallback(function (array $attributes) use (&$captured) {
            $captured = $attributes;

            return new SupportGroupType($attributes);
        });

        $this->service($repository)->create(['name' => 'Friends', 'sort_order' => 5], false);

        $this->assertSame(5, $captured['sort_order']);
        $this->assertFalse($captured['is_active']);
    }

    public function test_update_regenerates_the_slug(): void
    {
        $type = new SupportGroupType(['name' => 'Old Name', 'slug' => 'old-name']);

        $captured = null;
        $repository = $this->createMock(SupportGroupTypeRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('update')
            ->willReturnCallback(function (SupportGroupType $updated, array $attributes) use (&$captured) {
                $captured = $attributes;

                return true;
            });

        $result = $this->service($repository)->update($type, ['name' => 'New Name'], false);

        $this->assertSame('success', $result['status']);
        $this->assertSame('Support group type updated.', $result['message']);
        $this->assertSame('new-name', $captured['slug']);
        $this->assertFalse($captured['is_active']);
    }

    public function test_delete_blocks_a_group_that_has_contacts(): void
    {
        $type = new SupportGroupType(['name' => 'Friends']);

        $repository = $this->createMock(SupportGroupTypeRepositoryInterface::class);
        $repository->method('hasContacts')->willReturn(true);
        $repository->expects($this->never())->method('delete');

        $result = $this->service($repository)->delete($type);

        $this->assertSame('warning', $result['status']);
        $this->assertSame('This group has contacts. Deactivate it instead of deleting it.', $result['message']);
    }

    public function test_delete_removes_an_empty_group(): void
    {
        $type = new SupportGroupType(['name' => 'Friends']);

        $repository = $this->createMock(SupportGroupTypeRepositoryInterface::class);
        $repository->method('hasContacts')->willReturn(false);
        $repository->expects($this->once())->method('delete')->with($type)->willReturn(true);

        $result = $this->service($repository)->delete($type);

        $this->assertSame('success', $result['status']);
        $this->assertSame('Support group type deleted.', $result['message']);
    }
}
