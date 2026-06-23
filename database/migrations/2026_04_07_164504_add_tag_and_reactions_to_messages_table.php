<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('tag_id')->nullable()->constrained('tags')->onDelete('set null');
        });

        // New table for reactions
        Schema::create('message_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reaction', 10); // e.g. 👍, ❤️, 🔥, 😂
            $table->timestamps();

            $table->unique(['message_id', 'user_id']); // One reaction per user per message
        });
    }

    public function down()
    {
        Schema::dropIfExists('message_reactions');
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['tag_id']);
            $table->dropColumn('tag_id');
        });
    }
};