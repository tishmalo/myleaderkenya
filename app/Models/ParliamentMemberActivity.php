<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParliamentMemberActivity extends Model implements AuditableContract
{
    use AuditsChanges;
    protected $fillable = ['parliament_member_id', 'type', 'occurred_on', 'title', 'decision', 'source_url', 'metadata', 'sort_order'];
    protected function casts(): array { return ['occurred_on' => 'date', 'metadata' => 'array']; }
    public function member(): BelongsTo { return $this->belongsTo(ParliamentMember::class, 'parliament_member_id'); }
}
