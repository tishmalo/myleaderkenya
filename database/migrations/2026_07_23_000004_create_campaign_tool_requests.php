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
        if (! Schema::hasTable('campaign_tool_requests')) {
            Schema::create('campaign_tool_requests', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('campaign_tool_id')->constrained('campaign_tools')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('candidate_id')->nullable()->constrained('candidates')->nullOnDelete();
                $table->string('requester_name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('requested_feature');
                $table->text('use_case')->nullable();
                $table->string('status')->default('new');
                $table->text('admin_notes')->nullable();
                $table->timestamps();
                $table->index(['campaign_tool_id', 'status'], 'ctr_tool_status_idx');
            });
        }

        $now = now();
        $permissions = [
            ['campaign-tool-requests.view', 'View Campaign Tool Requests', 'Aspirant Campaigns'],
            ['campaign-tool-requests.update', 'Edit Campaign Tool Requests', 'Aspirant Campaigns'],
            ['campaign-tool-requests.delete', 'Delete Campaign Tool Requests', 'Aspirant Campaigns'],
        ];

        foreach ($permissions as $index => [$name, $label, $group]) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name],
                [
                    'label' => $label,
                    'group' => $group,
                    'sort_order' => 230 + $index,
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
        $permissionIds = DB::table('permissions')
            ->whereIn('name', [
                'campaign-tool-requests.view',
                'campaign-tool-requests.update',
                'campaign-tool-requests.delete',
            ])
            ->pluck('id');

        DB::table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
        Schema::dropIfExists('campaign_tool_requests');
    }
};