<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('campaign_tool_packages', 'token_cost')) {
            Schema::table('campaign_tool_packages', function (Blueprint $table): void {
                $table->unsignedBigInteger('token_cost')->nullable()->after('description');
            });
        }

        if (Schema::hasColumn('campaign_tool_packages', 'price')) {
            DB::table('campaign_tool_packages')
                ->whereNull('token_cost')
                ->orderBy('id')
                ->chunkById(200, function ($packages): void {
                    foreach ($packages as $package) {
                        DB::table('campaign_tool_packages')
                            ->where('id', $package->id)
                            ->update(['token_cost' => max(1, (int) ceil((float) $package->price))]);
                    }
                });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('campaign_tool_packages', 'token_cost')) {
            Schema::table('campaign_tool_packages', function (Blueprint $table): void {
                $table->dropColumn('token_cost');
            });
        }
    }
};
