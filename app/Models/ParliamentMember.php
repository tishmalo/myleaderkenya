<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParliamentMember extends Model implements AuditableContract
{
    use AuditsChanges;
    protected $fillable = [
        'external_slug', 'source_name', 'normalized_name', 'source_url', 'photo_url', 'house', 'role', 'constituency',
        'party', 'position_type', 'biography', 'speeches_last_year', 'speeches_total', 'bills_total', 'bills_pages',
        'raw_payload', 'detail_status', 'failure_code', 'detail_fetched_at', 'candidate_id', 'match_method',
        'matched_token_count', 'linked_by', 'linked_at', 'is_published', 'published_by', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'raw_payload' => 'array', 'detail_fetched_at' => 'datetime', 'linked_at' => 'datetime',
            'is_published' => 'boolean', 'published_at' => 'datetime',
        ];
    }

    public function candidate(): BelongsTo { return $this->belongsTo(Candidate::class); }
    public function linker(): BelongsTo { return $this->belongsTo(User::class, 'linked_by'); }
    public function publisher(): BelongsTo { return $this->belongsTo(User::class, 'published_by'); }
    public function committees(): HasMany { return $this->hasMany(ParliamentMemberCommittee::class)->orderBy('sort_order'); }
    public function activities(): HasMany { return $this->hasMany(ParliamentMemberActivity::class)->orderByDesc('occurred_on')->orderBy('sort_order'); }
}
