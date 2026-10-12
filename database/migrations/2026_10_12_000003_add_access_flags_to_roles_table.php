<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Roles were hardcoded to user / admin / superadmin: "can this account reach
 * the admin area" and "does this account bypass every permission check" were
 * decided by comparing role names in PHP, so a fourth role needed code changes.
 *
 * Move both decisions onto the role row itself, plus the display order and a
 * system flag that protects the three built in roles from being renamed or
 * deleted. Creating roles also gets its own permission.
 */
return new class extends Migration
{
    /**
     * @var array<string, array{grants_admin_access: bool, has_full_access: bool, sort_order: int}>
     */
    private const SYSTEM_ROLES = [
        Role::USER => ['grants_admin_access' => false, 'has_full_access' => false, 'sort_order' => 10],
        Role::ADMIN => ['grants_admin_access' => true, 'has_full_access' => false, 'sort_order' => 20],
        Role::SUPERADMIN => ['grants_admin_access' => true, 'has_full_access' => true, 'sort_order' => 30],
    ];

    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->string('description')->nullable();
            $table->boolean('grants_admin_access')->default(false);
            $table->boolean('has_full_access')->default(false);
            $table->boolean('is_system')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
        });

        foreach (self::SYSTEM_ROLES as $name => $flags) {
            DB::table('roles')->where('name', $name)->update($flags + ['is_system' => true]);
        }

        $now = now();

        DB::table('permissions')->updateOrInsert(
            ['name' => 'user-access.manage-roles'],
            [
                'label' => 'Create & Manage Roles',
                'group' => 'Access Control',
                'sort_order' => 35,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $superAdminRoleId = DB::table('roles')->where('name', Role::SUPERADMIN)->value('id');

        if ($superAdminRoleId) {
            DB::table('permission_role')->updateOrInsert(
                [
                    'role_id' => $superAdminRoleId,
                    'permission_id' => DB::table('permissions')->where('name', 'user-access.manage-roles')->value('id'),
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
        $permissionId = DB::table('permissions')->where('name', 'user-access.manage-roles')->value('id');

        if ($permissionId) {
            DB::table('permission_role')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }

        Schema::table('roles', function (Blueprint $table): void {
            $table->dropColumn(['description', 'grants_admin_access', 'has_full_access', 'is_system', 'sort_order']);
        });
    }
};
