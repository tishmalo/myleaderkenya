<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->string('slug')->unique();
            $table->string('poll_type')->default('words');
            $table->string('status')->default('draft');
            // dateTime, not timestamp: MySQL rejects a non-null TIMESTAMP with
            // no default under strict mode, and the events table already sets
            // the precedent for required date-times.
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at');
            $table->boolean('reveal_results')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // The homepage only ever asks for the live poll, so this composite
            // index backs both the status filter and the deadline ordering.
            $table->index(['status', 'ends_at'], 'polls_status_ends_idx');
        });

        Schema::create('poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained('polls')->cascadeOnDelete();
            $table->string('label');
            $table->foreignId('candidate_id')->nullable()->constrained('candidates')->nullOnDelete();
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->index(['poll_id', 'display_order'], 'poll_options_poll_order_idx');
        });

        Schema::create('poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained('polls')->cascadeOnDelete();
            $table->foreignId('poll_option_id')->constrained('poll_options')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Enforces one vote per account at the database level rather than
            // trusting the controller, so a double submit cannot slip through.
            $table->unique(['poll_id', 'user_id']);
            $table->index(['poll_option_id'], 'poll_votes_option_idx');
        });

        Schema::create('poll_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained('polls')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->timestamps();

            $table->index(['poll_id', 'status'], 'poll_comments_poll_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_comments');
        Schema::dropIfExists('poll_votes');
        Schema::dropIfExists('poll_options');
        Schema::dropIfExists('polls');
    }
};
