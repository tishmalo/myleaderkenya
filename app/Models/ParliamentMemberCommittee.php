<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParliamentMemberCommittee extends Model implements AuditableContract
{
    use AuditsChanges;
    protected $fillable = ['parliament_member_id', 'name', 'normalized_name', 'sort_order'];
    public function member(): BelongsTo { return $this->belongsTo(ParliamentMember::class, 'parliament_member_id'); }
}
