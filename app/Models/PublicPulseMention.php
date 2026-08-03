<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PublicPulseMention extends Model implements AuditableContract
{
    use AuditsChanges;
    protected $fillable = [
        'candidate_id',
        'source_key',
        'source_type',
        'external_id',
        'url',
        'content_hash',
        'author_name',
        'title',
        'text',
        'published_at',
        'engagement',
        'raw_payload',
        'language',
        'sentiment',
        'tone',
        'classification_confidence',
        'classified_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'engagement' => 'array',
        'raw_payload' => 'array',
        'classification_confidence' => 'float',
        'classified_at' => 'datetime',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function classification(): HasOne
    {
        return $this->hasOne(PublicPulseMentionClassification::class, 'mention_id');
    }
}
