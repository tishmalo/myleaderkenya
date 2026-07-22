<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('group');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['permission_id', 'role_id']);
        });

        $now = now();
        $permissions = [
            ['dashboard.view', 'View Dashboard', 'Dashboard'],
            ['dashboard.create', 'Add Dashboard', 'Dashboard'],
            ['dashboard.update', 'Edit Dashboard', 'Dashboard'],
            ['dashboard.delete', 'Delete Dashboard', 'Dashboard'],
            ['voters.view', 'View Voters', 'Voter Management'],
            ['voters.create', 'Add Voters', 'Voter Management'],
            ['voters.update', 'Edit Voters', 'Voter Management'],
            ['voters.delete', 'Delete Voters', 'Voter Management'],
            ['messages.view', 'View Messages', 'Communications'],
            ['messages.create', 'Send Messages', 'Communications'],
            ['live-stats.view', 'View Live Stat Figures', 'Voter Management'],
            ['live-stats.create', 'Add Live Stat Figures', 'Voter Management'],
            ['live-stats.update', 'Edit Live Stat Figures', 'Voter Management'],
            ['live-stats.delete', 'Delete Live Stat Figures', 'Voter Management'],
            ['aspirants.view', 'View Aspirants', 'Aspirant Campaigns'],
            ['aspirants.create', 'Add Aspirants', 'Aspirant Campaigns'],
            ['aspirants.update', 'Edit Aspirants', 'Aspirant Campaigns'],
            ['aspirants.delete', 'Delete Aspirants', 'Aspirant Campaigns'],
            ['aspirants.approve', 'Approve Aspirants', 'Aspirant Campaigns'],
            ['parties.view', 'View Parties', 'Political Structures'],
            ['parties.create', 'Add Parties', 'Political Structures'],
            ['parties.update', 'Edit Parties', 'Political Structures'],
            ['parties.delete', 'Delete Parties', 'Political Structures'],
            ['frontend.view', 'View Front End Settings', 'Public Content'],
            ['frontend.update', 'Edit Front End Settings', 'Public Content'],
            ['data.view', 'View Data', 'Geography Data'],
            ['data.create', 'Add Data', 'Geography Data'],
            ['data.update', 'Edit Data', 'Geography Data'],
            ['data.delete', 'Delete Data', 'Geography Data'],
            ['data.import', 'Import Data', 'Geography Data'],
            ['user-access.view', 'View User Access', 'Access Control'],
            ['user-access.create-admin', 'Create Admins', 'Access Control'],
            ['user-access.assign-role', 'Allocate Roles To Users', 'Access Control'],
            ['user-access.manage-permissions', 'Manage Role Permissions', 'Access Control'],
        ];

        foreach ($permissions as $index => [$name, $label, $group]) {
            DB::table('permissions')->insert([
                'name' => $name,
                'label' => $label,
                'group' => $group,
                'sort_order' => $index + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $superAdminRoleId = DB::table('roles')->where('name', Role::SUPERADMIN)->value('id');
        $adminRoleId = DB::table('roles')->where('name', Role::ADMIN)->value('id');
        $allPermissionIds = DB::table('permissions')->pluck('id');
        $adminPermissionIds = DB::table('permissions')
            ->whereNotIn('name', ['user-access.create-admin', 'user-access.assign-role', 'user-access.manage-permissions'])
            ->pluck('id');

        foreach ($allPermissionIds as $permissionId) {
            DB::table('permission_role')->insert([
                'role_id' => $superAdminRoleId,
                'permission_id' => $permissionId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($adminPermissionIds as $permissionId) {
            DB::table('permission_role')->insert([
                'role_id' => $adminRoleId,
                'permission_id' => $permissionId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
    }
};
