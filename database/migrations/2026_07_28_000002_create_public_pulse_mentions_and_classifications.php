<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('public_pulse_mentions')) {
            Schema::create('public_pulse_mentions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
                $table->string('source_key')->index();
                $table->string('source_type')->nullable()->index();
                $table->string('external_id')->nullable();
                $table->string('url', 1200)->nullable();
                $table->string('content_hash', 64)->index();
                $table->string('author_name')->nullable();
                $table->string('title', 500)->nullable();
                $table->text('text')->nullable();
                $table->timestamp('published_at')->nullable()->index();
                $table->json('engagement')->nullable();
                $table->json('raw_payload')->nullable();
                $table->string('language', 20)->nullable()->index();
                $table->string('sentiment', 30)->nullable()->index();
                $table->string('tone', 40)->nullable()->index();
                $table->decimal('classification_confidence', 4, 3)->nullable()->index();
                $table->timestamp('classified_at')->nullable()->index();
                $table->timestamps();

                $table->unique(['candidate_id', 'source_key', 'external_id'], 'pulse_mentions_candidate_source_external_unique');
            });
        } else {
            Schema::table('public_pulse_mentions', function (Blueprint $table): void {
                if (! Schema::hasColumn('public_pulse_mentions', 'language')) {
                    $table->string('language', 20)->nullable()->index();
                }

                if (! Schema::hasColumn('public_pulse_mentions', 'sentiment')) {
                    $table->string('sentiment', 30)->nullable()->index();
                }

                if (! Schema::hasColumn('public_pulse_mentions', 'tone')) {
                    $table->string('tone', 40)->nullable()->index();
                }

                if (! Schema::hasColumn('public_pulse_mentions', 'classification_confidence')) {
                    $table->decimal('classification_confidence', 4, 3)->nullable()->index();
                }

                if (! Schema::hasColumn('public_pulse_mentions', 'classified_at')) {
                    $table->timestamp('classified_at')->nullable()->index();
                }
            });
        }

        if (! Schema::hasTable('public_pulse_mention_classifications')) {
            Schema::create('public_pulse_mention_classifications', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('mention_id')->nullable()->constrained('public_pulse_mentions')->nullOnDelete();
                $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
                $table->string('content_hash', 64);
                $table->string('language', 20)->default('unknown')->index();
                $table->string('translated_summary', 700)->nullable();
                $table->string('sentiment', 30)->default('neutral')->index();
                $table->string('tone', 40)->default('unclear')->index();
                $table->string('emotion', 40)->default('none')->index();
                $table->string('toxicity', 20)->default('none')->index();
                $table->boolean('sarcasm')->default(false)->index();
                $table->json('topics')->nullable();
                $table->string('stance', 40)->default('mentions_candidate')->index();
                $table->decimal('confidence', 4, 3)->default(0);
                $table->string('model_name')->nullable();
                $table->string('prompt_version', 40)->index();
                $table->unsignedInteger('input_tokens')->default(0);
                $table->unsignedInteger('output_tokens')->default(0);
                $table->json('raw_json')->nullable();
                $table->timestamp('classified_at')->nullable()->index();
                $table->timestamps();

                $table->unique(['candidate_id', 'content_hash', 'prompt_version'], 'pulse_classification_cache_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('public_pulse_mention_classifications');

        if (Schema::hasTable('public_pulse_mentions')) {
            Schema::table('public_pulse_mentions', function (Blueprint $table): void {
                foreach (['language', 'sentiment', 'tone', 'classification_confidence', 'classified_at'] as $column) {
                    if (Schema::hasColumn('public_pulse_mentions', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
