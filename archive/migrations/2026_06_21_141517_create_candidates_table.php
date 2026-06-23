<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nick_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('position_id')->constrained('positions')->onDelete('cascade');
            
            $table->string('profile_picture')->nullable();
            $table->text('about')->nullable();

            // Kenyan Administrative Hierarchy
            $table->string('country')->default('Kenya');
            $table->string('county')->nullable();
            $table->string('constituency')->nullable();
            $table->string('ward')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('candidates');
    }
};