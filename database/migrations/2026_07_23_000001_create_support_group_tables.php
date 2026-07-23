<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('support_group_types')) {
            Schema::create('support_group_types', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 100)->unique();
                $table->string('slug', 120)->unique();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('candidate_support_contacts')) {
            Schema::create('candidate_support_contacts', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
                $table->foreignId('support_group_type_id')->constrained()->restrictOnDelete();
                $table->string('name');
                $table->text('email')->nullable();
                $table->text('phone')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['candidate_id', 'support_group_type_id'], 'csc_candidate_group_idx');
            });
        } elseif (! $this->indexExists('candidate_support_contacts', 'csc_candidate_group_idx')) {
            Schema::table('candidate_support_contacts', function (Blueprint $table): void {
                $table->index(['candidate_id', 'support_group_type_id'], 'csc_candidate_group_idx');
            });
        }

        $now = now();
        foreach (['Friends', 'Family'] as $index => $name) {
            DB::table('support_group_types')->updateOrInsert(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_support_contacts');
        Schema::dropIfExists('support_group_types');
    }

    private function indexExists(string $table, string $index): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::connection()->getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};