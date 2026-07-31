<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_tool_request_selected_tools', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_tool_request_id');
            $table->foreignId('campaign_tool_id');
            $table->foreign('campaign_tool_request_id', 'ctr_selected_request_fk')
                ->references('id')
                ->on('campaign_tool_requests')
                ->cascadeOnDelete();
            $table->foreign('campaign_tool_id', 'ctr_selected_tool_fk')
                ->references('id')
                ->on('campaign_tools')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(
                ['campaign_tool_request_id', 'campaign_tool_id'],
                'ctr_selected_tool_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_tool_request_selected_tools');
    }
};