<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('news_article_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_article_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['news_article_id', 'category_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('news_article_category');
    }
};