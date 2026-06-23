<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default tags
        $tags = [
            'politics', 'technology', 'education', 'insecurity', 'youth', 
            'women', 'agriculture', 'infrastructure', 'economy', 'environment',
            'housing', 'sports', 'tourism', 'industrialization'
        ];

        foreach ($tags as $tag) {
            \DB::table('tags')->insert([
                'name' => ucfirst($tag),
                'slug' => $tag,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('tags');
    }
};