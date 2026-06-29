<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            if (! Schema::hasColumn('news_articles', 'sentiment')) {
                $table->enum('sentiment', ['neutral', 'positive', 'negative'])->default('neutral')->after('status');
            }
        });

        if (! Schema::hasTable('news_article_political_party')) {
            Schema::create('news_article_political_party', function (Blueprint $table) {
                $table->id();
                $table->foreignId('news_article_id')->constrained()->onDelete('cascade');
                $table->foreignId('political_party_id')->constrained()->onDelete('cascade');
                $table->timestamps();

                $table->unique(['news_article_id', 'political_party_id'], 'news_article_party_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('news_article_political_party');

        Schema::table('news_articles', function (Blueprint $table) {
            if (Schema::hasColumn('news_articles', 'sentiment')) {
                $table->dropColumn('sentiment');
            }
        });
    }
};
