<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Clerks (the admin role) run the day-to-day public content queue: adding
 * links/pages, creating polls and publishing news. The whole Public Content
 * module is gated by frontend.view (read) and frontend.update (write), so make
 * sure both are attached to the admin role, not only the super admin.
 */
return new class extends Migration
{
    /**
     * @var list<array{0: string, 1: string, 2: string, 3: int}>
     */
    private const PERMISSIONS = [
        ['frontend.view', 'View Front End Settings', 'Public Content', 24],
        ['frontend.update', 'Edit Front End Settings', 'Public Content', 25],
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::PERMISSIONS as [$name, $label, $group, $sortOrder]) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name],
                [
                    'label' => $label,
                    'group' => $group,
                    'sort_order' => $sortOrder,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $adminRoleId = DB::table('roles')->where('name', Role::ADMIN)->value('id');

        if (! $adminRoleId) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', array_column(self::PERMISSIONS, 0))
            ->pluck('id');

        foreach ($permissionIds as $permissionId) {
            DB::table('permission_role')->updateOrInsert(
                [
                    'role_id' => $adminRoleId,
                    'permission_id' => $permissionId,
                ],
                [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        $adminRoleId = DB::table('roles')->where('name', Role::ADMIN)->value('id');

        if (! $adminRoleId) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', array_column(self::PERMISSIONS, 0))
            ->pluck('id');

        DB::table('permission_role')
            ->where('role_id', $adminRoleId)
            ->whereIn('permission_id', $permissionIds)
            ->delete();
    }
};
