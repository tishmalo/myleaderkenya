<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Public Content used to be guarded by two blanket permissions
 * (frontend.view / frontend.update), which meant a clerk who could add a link
 * could also publish news, run polls, regenerate the sitemap and manage Public
 * Pulse. Split that into module specific permissions so access can be granted
 * as narrowly as the rest of the system.
 *
 * The two legacy rows are renamed rather than replaced, so the ids - and with
 * them every existing role grant - survive the split.
 */
return new class extends Migration
{
    private const GROUP = 'Public Content';

    private const FIRST_SORT_ORDER = 24;

    /**
     * Legacy blanket permission => [replacement name, legacy label, legacy sort order].
     *
     * @var array<string, array{0: string, 1: string, 2: int}>
     */
    private const LEGACY_RENAMES = [
        'frontend.view' => ['frontend-pages.view', 'View Front End Settings', 24],
        'frontend.update' => ['frontend-pages.update', 'Edit Front End Settings', 25],
    ];

    /**
     * The full Public Content permission set, in display order.
     *
     * @var list<array{0: string, 1: string}>
     */
    private const PERMISSIONS = [
        ['frontend-pages.view', 'View Frontend Pages'],
        ['frontend-pages.update', 'Edit Frontend Pages'],

        ['news.view', 'View News'],
        ['news.create', 'Add News'],
        ['news.update', 'Edit News'],
        ['news.delete', 'Delete News'],
        ['news-comments.moderate', 'Moderate News Comments'],

        ['polls.view', 'View Polls'],
        ['polls.create', 'Add Polls'],
        ['polls.update', 'Edit Polls'],
        ['polls.delete', 'Delete Polls'],
        ['poll-comments.moderate', 'Moderate Poll Comments'],

        ['events.view', 'View Events'],
        ['events.create', 'Add Events'],
        ['events.update', 'Edit Events'],
        ['events.delete', 'Delete Events'],
        ['events.approve', 'Approve Events'],

        ['links.view', 'View Pages & Links'],
        ['links.create', 'Add Pages & Links'],
        ['links.update', 'Edit Pages & Links'],
        ['links.delete', 'Delete Pages & Links'],
        ['links.approve', 'Approve Pages & Links'],

        ['sitemap.view', 'View Sitemap'],
        ['sitemap.regenerate', 'Regenerate Sitemap'],

        ['tags.view', 'View Tags'],
        ['tags.create', 'Add Tags'],
        ['tags.delete', 'Delete Tags'],

        ['public-pulse.view', 'View Public Pulse'],
        ['public-pulse.create', 'Add Public Pulse Jobs'],
        ['public-pulse.update', 'Manage Public Pulse'],
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::LEGACY_RENAMES as $legacy => [$replacement]) {
            DB::table('permissions')
                ->where('name', $legacy)
                ->update(['name' => $replacement, 'updated_at' => $now]);
        }

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

        // Clerks and super admins keep the read + write reach the blanket
        // permissions gave them; narrow it per module from the admin UI.
        $roleIds = DB::table('roles')
            ->whereIn('name', [Role::SUPERADMIN, Role::ADMIN])
            ->pluck('id');

        $permissionIds = DB::table('permissions')
            ->whereIn('name', array_column(self::PERMISSIONS, 0))
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
                DB::table('permission_role')->updateOrInsert(
                    [
                        'role_id' => $roleId,
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
        $now = now();
        $restored = array_map(fn (array $legacy): string => $legacy[0], array_values(self::LEGACY_RENAMES));
        $added = array_values(array_diff(array_column(self::PERMISSIONS, 0), $restored));

        $addedIds = DB::table('permissions')->whereIn('name', $added)->pluck('id');

        DB::table('permission_role')->whereIn('permission_id', $addedIds)->delete();
        DB::table('permissions')->whereIn('id', $addedIds)->delete();

        foreach (self::LEGACY_RENAMES as $legacy => [$replacement, $label, $sortOrder]) {
            DB::table('permissions')
                ->where('name', $replacement)
                ->update([
                    'name' => $legacy,
                    'label' => $label,
                    'sort_order' => $sortOrder,
                    'updated_at' => $now,
                ]);
        }
    }
};
