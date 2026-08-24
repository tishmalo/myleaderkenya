<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndex('candidates', ['approval_status', 'created_at'], 'cand_approval_created_idx');
        $this->addIndex('candidates', ['approval_status', 'featured', 'created_at'], 'cand_approval_featured_created_idx');
        $this->addIndex('candidates', ['approval_status', 'position_id', 'created_at'], 'cand_approval_position_created_idx');
        $this->addIndex('candidates', ['approval_status', 'political_party_id', 'created_at'], 'cand_approval_party_created_idx');
        $this->addIndex('candidates', ['approval_status', 'county', 'created_at'], 'cand_approval_county_created_idx');
        $this->addIndex('candidates', ['approval_status', 'constituency', 'created_at'], 'cand_approval_constituency_created_idx');
        $this->addIndex('candidates', ['approval_status', 'ward', 'created_at'], 'cand_approval_ward_created_idx');
        $this->addIndex('candidates', ['user_id', 'created_at'], 'cand_user_created_idx');

        $this->addIndex('users', ['is_voter'], 'users_is_voter_idx');
        $this->addIndex('users', ['is_registered'], 'users_is_registered_idx');
        $this->addIndex('users', ['voter_registered'], 'users_voter_registered_idx');
        $this->addIndex('users', ['gender', 'is_registered'], 'users_gender_registered_idx');
        $this->addIndex('users', ['is_voter', 'county'], 'users_voter_county_idx');
        $this->addIndex('users', ['is_registered', 'county'], 'users_registered_county_idx');
        $this->addIndex('users', ['is_voter', 'constituency'], 'users_voter_constituency_idx');
        $this->addIndex('users', ['is_registered', 'constituency'], 'users_registered_constituency_idx');
        $this->addIndex('users', ['is_voter', 'ward'], 'users_voter_ward_idx');
        $this->addIndex('users', ['is_registered', 'ward'], 'users_registered_ward_idx');
        $this->addIndex('users', ['role_id', 'created_at'], 'users_role_created_idx');

        $this->addIndex('news_articles', ['status', 'published_at', 'created_at'], 'news_status_published_created_idx');
        $this->addIndex('news_articles', ['author_id', 'created_at'], 'news_author_created_idx');

        $this->addIndex('political_parties', ['status', 'sort_order'], 'parties_status_sort_idx');
        $this->addIndex('coalitions', ['status', 'sort_order'], 'coalitions_status_sort_idx');
        $this->addIndex('campaign_tools', ['status', 'sort_order'], 'campaign_tools_status_sort_idx');
        $this->addIndex('payment_methods', ['is_active', 'sort_order'], 'payment_methods_active_sort_idx');

        $this->addIndex('messages', ['county', 'constituency', 'created_at'], 'messages_county_const_created_idx');
        $this->addIndex('messages', ['ward', 'created_at'], 'messages_ward_created_idx');
        $this->addIndex('messages', ['tag_id', 'created_at'], 'messages_tag_created_idx');

        $this->addIndex('polling_stations', ['county', 'constituency', 'office'], 'polling_county_const_office_idx');
        $this->addIndex('polling_stations', ['ward', 'office'], 'polling_ward_office_idx');
        $this->addIndex('polling_stations', ['bloc_id', 'county'], 'polling_bloc_county_idx');

        $this->addIndex('group_members', ['user_id', 'group_id'], 'group_members_user_group_idx');
        $this->addIndex('groups', ['created_by', 'created_at'], 'groups_creator_created_idx');
        $this->addIndex('group_messages', ['group_id', 'created_at'], 'group_messages_group_created_idx');
        $this->addIndex('group_messages', ['aspirant_poll_id', 'created_at'], 'group_messages_poll_created_idx');

        $this->addIndex('aspirant_polls', ['candidate_id', 'created_at'], 'aspirant_polls_candidate_created_idx');
        $this->addIndex('aspirant_polls', ['candidate_id', 'status', 'created_at'], 'aspirant_polls_candidate_status_created_idx');
        $this->addIndex('aspirant_polls', ['status', 'published_at'], 'aspirant_polls_status_published_idx');
        $this->addIndex('aspirant_polls', ['scope_column', 'scope_value'], 'aspirant_polls_scope_idx');

        $this->addIndex('candidate_sms_messages', ['candidate_id', 'created_at'], 'candidate_sms_candidate_created_idx');
        $this->addIndex('candidate_sms_messages', ['candidate_id', 'status', 'created_at'], 'candidate_sms_candidate_status_created_idx');
        $this->addIndex('candidate_sms_messages', ['scope_column', 'scope_value'], 'candidate_sms_scope_idx');
        $this->addIndex('candidate_sms_messages', ['token_transaction_id'], 'candidate_sms_token_transaction_idx');

        $this->addIndex('campaign_website_requests', ['candidate_id', 'created_at'], 'campaign_web_candidate_created_idx');
        $this->addIndex('campaign_website_requests', ['status', 'created_at'], 'campaign_web_status_created_idx');
        $this->addIndex('campaign_tool_requests', ['candidate_id', 'created_at'], 'campaign_tool_req_candidate_created_idx');
        $this->addIndex('campaign_tool_requests', ['status', 'created_at'], 'campaign_tool_req_status_created_idx');

        $this->addIndex('candidate_call_logs', ['candidate_id', 'callback_at'], 'candidate_calls_callback_idx');
        $this->addIndex('candidate_token_purchases', ['candidate_id', 'created_at'], 'candidate_token_purchases_candidate_created_idx');
        $this->addIndex('candidate_token_purchases', ['candidate_id', 'status', 'created_at'], 'candidate_token_purchases_status_created_idx');
        $this->addIndex('candidate_token_transactions', ['candidate_id', 'created_at'], 'candidate_token_tx_candidate_created_idx');
        $this->addIndex('candidate_token_transactions', ['candidate_token_wallet_id', 'created_at'], 'candidate_token_tx_wallet_created_idx');
        $this->addIndex('candidate_sms_balance_requests', ['candidate_id', 'created_at'], 'candidate_sms_balance_candidate_created_idx');
        $this->addIndex('candidate_sms_balance_requests', ['status', 'created_at'], 'candidate_sms_balance_status_created_idx');
    }

    public function down(): void
    {
        foreach ($this->indexes() as [$table, $index]) {
            $this->dropIndex($table, $index);
        }
    }

    private function addIndex(string $table, array $columns, string $index): void
    {
        if (! Schema::hasTable($table) || $this->indexExists($table, $index)) {
            return;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return;
            }
        }

        Schema::table($table, function (Blueprint $table) use ($columns, $index): void {
            $table->index($columns, $index);
        });
    }

    private function dropIndex(string $table, string $index): void
    {
        if (! Schema::hasTable($table) || ! $this->indexExists($table, $index)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($index): void {
            $table->dropIndex($index);
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return collect(DB::select("PRAGMA index_list(\"" . str_replace('"', '', $table) . "\")"))
                ->contains(fn ($row) => ($row->name ?? null) === $index);
        }

        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::connection()->getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }

    private function indexes(): array
    {
        return [
            ['candidates', 'cand_approval_created_idx'],
            ['candidates', 'cand_approval_featured_created_idx'],
            ['candidates', 'cand_approval_position_created_idx'],
            ['candidates', 'cand_approval_party_created_idx'],
            ['candidates', 'cand_approval_county_created_idx'],
            ['candidates', 'cand_approval_constituency_created_idx'],
            ['candidates', 'cand_approval_ward_created_idx'],
            ['candidates', 'cand_user_created_idx'],
            ['users', 'users_is_voter_idx'],
            ['users', 'users_is_registered_idx'],
            ['users', 'users_voter_registered_idx'],
            ['users', 'users_gender_registered_idx'],
            ['users', 'users_voter_county_idx'],
            ['users', 'users_registered_county_idx'],
            ['users', 'users_voter_constituency_idx'],
            ['users', 'users_registered_constituency_idx'],
            ['users', 'users_voter_ward_idx'],
            ['users', 'users_registered_ward_idx'],
            ['users', 'users_role_created_idx'],
            ['news_articles', 'news_status_published_created_idx'],
            ['news_articles', 'news_author_created_idx'],
            ['political_parties', 'parties_status_sort_idx'],
            ['coalitions', 'coalitions_status_sort_idx'],
            ['campaign_tools', 'campaign_tools_status_sort_idx'],
            ['payment_methods', 'payment_methods_active_sort_idx'],
            ['messages', 'messages_county_const_created_idx'],
            ['messages', 'messages_ward_created_idx'],
            ['messages', 'messages_tag_created_idx'],
            ['polling_stations', 'polling_county_const_office_idx'],
            ['polling_stations', 'polling_ward_office_idx'],
            ['polling_stations', 'polling_bloc_county_idx'],
            ['group_members', 'group_members_user_group_idx'],
            ['groups', 'groups_creator_created_idx'],
            ['group_messages', 'group_messages_group_created_idx'],
            ['group_messages', 'group_messages_poll_created_idx'],
            ['aspirant_polls', 'aspirant_polls_candidate_created_idx'],
            ['aspirant_polls', 'aspirant_polls_candidate_status_created_idx'],
            ['aspirant_polls', 'aspirant_polls_status_published_idx'],
            ['aspirant_polls', 'aspirant_polls_scope_idx'],
            ['candidate_sms_messages', 'candidate_sms_candidate_created_idx'],
            ['candidate_sms_messages', 'candidate_sms_candidate_status_created_idx'],
            ['candidate_sms_messages', 'candidate_sms_scope_idx'],
            ['candidate_sms_messages', 'candidate_sms_token_transaction_idx'],
            ['campaign_website_requests', 'campaign_web_candidate_created_idx'],
            ['campaign_website_requests', 'campaign_web_status_created_idx'],
            ['campaign_tool_requests', 'campaign_tool_req_candidate_created_idx'],
            ['campaign_tool_requests', 'campaign_tool_req_status_created_idx'],
            ['candidate_call_logs', 'candidate_calls_callback_idx'],
            ['candidate_token_purchases', 'candidate_token_purchases_candidate_created_idx'],
            ['candidate_token_purchases', 'candidate_token_purchases_status_created_idx'],
            ['candidate_token_transactions', 'candidate_token_tx_candidate_created_idx'],
            ['candidate_token_transactions', 'candidate_token_tx_wallet_created_idx'],
            ['candidate_sms_balance_requests', 'candidate_sms_balance_candidate_created_idx'],
            ['candidate_sms_balance_requests', 'candidate_sms_balance_status_created_idx'],
        ];
    }
};
