<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_stat_figures', function (Blueprint $table) {
            $table->id();
            $table->string('metric_key');
            $table->string('label');
            $table->unsignedBigInteger('value')->default(0);
            $table->string('source')->default('generated');
            $table->string('batch_id')->nullable()->index();
            $table->string('batch_name')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_stat_figures');
    }
};
