<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The Coordinators Guide is an internal handbook rather than a public module, so
 * its access is granted through the normal permission system: every role that
 * may already sign in to the admin area can read it, and only admin and super
 * admin roles start out able to rewrite it.
 */
return new class extends Migration
{
    private const GROUP = 'Team Guide';

    private const FIRST_SORT_ORDER = 70;

    /**
     * @var list<array{0: string, 1: string}>
     */
    private const PERMISSIONS = [
        ['guide.view', 'Read the Coordinators Guide'],
        ['guide.update', 'Edit the Coordinators Guide'],
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::PERMISSIONS as $index => [$name, $label]) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name],
                [
                    'label' => $label,
                    'group' => self::GROUP,
                    'sort_order' => self::FIRST_SORT_ORDER + $index,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', array_column(self::PERMISSIONS, 0))
            ->pluck('id', 'name');

        // Reading: everyone who can already enter the admin area.
        $staffRoles = DB::table('roles');

        if (Schema::hasColumn('roles', 'grants_admin_access')) {
            $staffRoles->where('grants_admin_access', true);
        } else {
            $staffRoles->whereIn('name', [Role::ADMIN, Role::SUPERADMIN]);
        }

        $staffRoleIds = $staffRoles->pluck('id')
            ->merge(DB::table('roles')->whereIn('name', [Role::ADMIN, Role::SUPERADMIN])->pluck('id'))
            ->unique();

        foreach ($staffRoleIds as $roleId) {
            $this->grant((int) $roleId, (int) $permissionIds['guide.view'], $now);
        }

        // Writing: the two roles that run the platform.
        $editorRoleIds = DB::table('roles')
            ->whereIn('name', [Role::ADMIN, Role::SUPERADMIN])
            ->pluck('id');

        foreach ($editorRoleIds as $roleId) {
            $this->grant((int) $roleId, (int) $permissionIds['guide.update'], $now);
        }
    }

    public function down(): void
    {
        $ids = DB::table('permissions')
            ->whereIn('name', array_column(self::PERMISSIONS, 0))
            ->pluck('id');

        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }

    private function grant(int $roleId, int $permissionId, mixed $now): void
    {
        DB::table('permission_role')->updateOrInsert(
            ['role_id' => $roleId, 'permission_id' => $permissionId],
            ['created_at' => $now, 'updated_at' => $now]
        );
    }
};
