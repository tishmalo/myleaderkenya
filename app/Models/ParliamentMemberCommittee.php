<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParliamentMemberCommittee extends Model
{
    protected $fillable = ['parliament_member_id', 'name', 'normalized_name', 'sort_order'];
    public function member(): BelongsTo { return $this->belongsTo(ParliamentMember::class, 'parliament_member_id'); }
}