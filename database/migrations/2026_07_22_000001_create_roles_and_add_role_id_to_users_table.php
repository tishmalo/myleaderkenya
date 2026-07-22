<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->timestamps();
        });

        $now = now();

        DB::table('roles')->insert([
            ['name' => 'user', 'label' => 'User', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'admin', 'label' => 'Admin', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'superadmin', 'label' => 'Super Admin', 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('role_id')->nullable()->after('role')->constrained('roles')->nullOnDelete();
        });

        $userRoleId = DB::table('roles')->where('name', 'user')->value('id');
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
        $superAdminRoleId = DB::table('roles')->where('name', 'superadmin')->value('id');

        DB::table('users')
            ->where('username', 'admin')
            ->update(['role_id' => $superAdminRoleId, 'role' => 'admin']);

        DB::table('users')
            ->where('username', '!=', 'admin')
            ->where('role', 'admin')
            ->update(['role_id' => $adminRoleId]);

        DB::table('users')
            ->whereNull('role_id')
            ->update(['role_id' => $userRoleId]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::dropIfExists('roles');
    }
};
