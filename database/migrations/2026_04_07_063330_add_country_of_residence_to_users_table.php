<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('country_of_residence')->nullable()->after('year_of_birth');
    });
}
//     public function up()
// {
//     Schema::table('users', function (Blueprint $table) {
//         $table->string('country_of_residence')->nullable()->after('ward');
//     });
// }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
