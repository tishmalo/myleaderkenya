<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'relationship')) {
                $table->string('relationship')->nullable()->after('is_aspirant');
            }
        });

        if (! Schema::hasTable('candidate_user_relationships')) {
            Schema::create('candidate_user_relationships', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
                $table->string('relationship')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'candidate_id']);
                $table->index(['candidate_id', 'relationship']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_user_relationships');

        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'relationship')) {
                $table->dropColumn('relationship');
            }
        });
    }
};