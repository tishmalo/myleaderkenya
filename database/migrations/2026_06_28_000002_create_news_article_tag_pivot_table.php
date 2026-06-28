<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('news_article_tag')) {
            Schema::create('news_article_tag', function (Blueprint $table) {
                $table->id();
                $table->foreignId('news_article_id')->constrained()->onDelete('cascade');
                $table->foreignId('tag_id')->constrained()->onDelete('cascade');
                $table->timestamps();

                $table->unique(['news_article_id', 'tag_id']);
            });
        }

        if (! Schema::hasTable('categories') || ! Schema::hasTable('news_article_category') || ! Schema::hasTable('tags')) {
            return;
        }

        $now = now();
        $categoryTagIds = [];

        DB::table('categories')->orderBy('id')->chunkById(100, function ($categories) use (&$categoryTagIds, $now) {
            foreach ($categories as $category) {
                $tag = DB::table('tags')->where('slug', $category->slug)->first();

                if (! $tag) {
                    $tagId = DB::table('tags')->insertGetId([
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'description' => $category->description,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                } else {
                    $tagId = $tag->id;
                }

                $categoryTagIds[$category->id] = $tagId;
            }
        });

        DB::table('news_article_category')->orderBy('id')->chunkById(200, function ($links) use (&$categoryTagIds, $now) {
            foreach ($links as $link) {
                if (! isset($categoryTagIds[$link->category_id])) {
                    continue;
                }

                DB::table('news_article_tag')->updateOrInsert(
                    [
                        'news_article_id' => $link->news_article_id,
                        'tag_id' => $categoryTagIds[$link->category_id],
                    ],
                    [
                        'created_at' => $link->created_at ?? $now,
                        'updated_at' => $link->updated_at ?? $now,
                    ]
                );
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_article_tag');
    }
};
