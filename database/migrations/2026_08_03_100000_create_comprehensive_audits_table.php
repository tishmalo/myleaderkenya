<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { if (!Schema::hasTable('audits')) Schema::create('audits', function (Blueprint $table): void {
        $table->bigIncrements('id'); $table->unsignedBigInteger('candidate_id')->nullable()->index();
        $table->string('user_type')->nullable(); $table->unsignedBigInteger('user_id')->nullable(); $table->index(['user_id','user_type']);
        $table->string('event'); $table->string('module')->nullable()->index(); $table->string('status',30)->default('success')->index(); $table->text('summary')->nullable();
        $table->uuid('correlation_id')->nullable()->index(); $table->string('batch_id')->nullable()->index(); $table->morphs('auditable');
        $table->text('old_values')->nullable(); $table->text('new_values')->nullable(); $table->json('metadata')->nullable();
        $table->text('url')->nullable(); $table->ipAddress('ip_address')->nullable(); $table->string('user_agent',1023)->nullable(); $table->string('tags')->nullable(); $table->timestamps();
    });
        else {
            $columns = [
                'candidate_id' => fn (Blueprint $table) => $table->unsignedBigInteger('candidate_id')->nullable()->index(),
                'module' => fn (Blueprint $table) => $table->string('module')->nullable()->index(),
                'status' => fn (Blueprint $table) => $table->string('status', 30)->default('success')->index(),
                'summary' => fn (Blueprint $table) => $table->text('summary')->nullable(),
                'correlation_id' => fn (Blueprint $table) => $table->uuid('correlation_id')->nullable()->index(),
                'batch_id' => fn (Blueprint $table) => $table->string('batch_id')->nullable()->index(),
                'metadata' => fn (Blueprint $table) => $table->json('metadata')->nullable(),
            ];
            foreach ($columns as $column => $definition) {
                if (! Schema::hasColumn('audits', $column)) Schema::table('audits', $definition);
            }
        }
    }
    public function down(): void { Schema::dropIfExists('audits'); }
};


