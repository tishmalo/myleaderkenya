<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $permissions = [
            ['support-groups.view', 'View Support Groups', 'Aspirant Campaigns'],
            ['support-groups.create', 'Add Support Groups', 'Aspirant Campaigns'],
            ['support-groups.update', 'Edit Support Groups', 'Aspirant Campaigns'],
            ['support-groups.delete', 'Delete Support Groups', 'Aspirant Campaigns'],
        ];

        foreach ($permissions as $index => [$name, $label, $group]) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name],
                [
                    'label' => $label,
                    'group' => $group,
                    'sort_order' => 140 + $index,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $roleIds = DB::table('roles')
            ->whereIn('name', [Role::SUPERADMIN, Role::ADMIN])
            ->pluck('id');

        $permissionIds = DB::table('permissions')
            ->whereIn('name', array_column($permissions, 0))
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
                DB::table('permission_role')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $permissionId],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->whereIn('name', [
                'support-groups.view',
                'support-groups.create',
                'support-groups.update',
                'support-groups.delete',
            ])
            ->pluck('id');

        DB::table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
