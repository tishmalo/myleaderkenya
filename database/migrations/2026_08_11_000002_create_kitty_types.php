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
        Schema::create('kitty_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        $names = ['Sacco Boost', 'Saving', 'Business Boost', 'Self Help Group', 'Chama Boost', 'Other'];
        DB::table('kitty_types')->insert(collect($names)->map(fn (string $name, int $index): array => [
            'name' => $name,
            'slug' => Str::slug($name, '_'),
            'sort_order' => $index,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all());

        Schema::table('user_token_purchases', function (Blueprint $table): void {
            $table->foreignId('kitty_type_id')->nullable()->after('kitty_type')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('user_token_purchases', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('kitty_type_id');
        });
        Schema::dropIfExists('kitty_types');
    }
};
