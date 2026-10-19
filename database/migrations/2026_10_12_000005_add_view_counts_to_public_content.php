<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Public content gets a view counter, so the popular stories, aspirants, polls
 * and parties can be identified instead of guessed at.
 */
return new class extends Migration
{
    /**
     * @var list<string>
     */
    private const TABLES = ['news_articles', 'candidates', 'polls', 'political_parties'];

    public function up(): void
    {
        foreach (self::TABLES as $name) {
            if (! Schema::hasTable($name) || Schema::hasColumn($name, 'views')) {
                continue;
            }

            Schema::table($name, function (Blueprint $table): void {
                $table->unsignedBigInteger('views')->default(0)->index();
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $name) {
            if (! Schema::hasTable($name) || ! Schema::hasColumn($name, 'views')) {
                continue;
            }

            Schema::table($name, function (Blueprint $table): void {
                $table->dropColumn('views');
            });
        }
    }
};
