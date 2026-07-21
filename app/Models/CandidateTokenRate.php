<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CandidateTokenRate extends Model
{
    public const CALCULATION_TYPES = ['fixed', 'per_recipient', 'per_sms_unit'];

    protected $fillable = [
        'action_key',
        'label',
        'calculation_type',
        'token_amount',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }
}
