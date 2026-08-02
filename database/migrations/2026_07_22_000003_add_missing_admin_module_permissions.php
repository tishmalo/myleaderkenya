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
            ['tokens.view', 'View Token Management', 'Token Management'],
            ['tokens.create', 'Add Token Management', 'Token Management'],
            ['tokens.update', 'Edit Token Management', 'Token Management'],
            ['tokens.delete', 'Delete Token Management', 'Token Management'],
            ['finance.view', 'View Finance', 'Finance'],
            ['finance.create', 'Add Finance', 'Finance'],
            ['finance.update', 'Edit Finance', 'Finance'],
            ['finance.delete', 'Delete Finance', 'Finance'],
            ['settings.view', 'View System Settings', 'System Settings'],
            ['settings.update', 'Edit System Settings', 'System Settings'],
        ];

        foreach ($permissions as $index => [$name, $label, $group]) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name],
                [
                    'label' => $label,
                    'group' => $group,
                    'sort_order' => 100 + $index,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $superAdminRoleId = DB::table('roles')->where('name', Role::SUPERADMIN)->value('id');

        if ($superAdminRoleId) {
            $permissionIds = DB::table('permissions')
                ->whereIn('name', array_column($permissions, 0))
                ->pluck('id');

            foreach ($permissionIds as $permissionId) {
                DB::table('permission_role')->updateOrInsert(
                    [
                        'role_id' => $superAdminRoleId,
                        'permission_id' => $permissionId,
                    ],
                    [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        DB::table('permissions')
            ->whereIn('name', [
                'tokens.view',
                'tokens.create',
                'tokens.update',
                'tokens.delete',
                'finance.view',
                'finance.create',
                'finance.update',
                'finance.delete',
                'settings.view',
                'settings.update',
            ])
            ->delete();
    }
};
