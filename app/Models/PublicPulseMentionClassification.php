<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicPulseMentionClassification extends Model
{
    protected $fillable = [
        'mention_id',
        'candidate_id',
        'content_hash',
        'language',
        'translated_summary',
        'sentiment',
        'tone',
        'emotion',
        'toxicity',
        'sarcasm',
        'topics',
        'stance',
        'confidence',
        'model_name',
        'prompt_version',
        'input_tokens',
        'output_tokens',
        'raw_json',
        'classified_at',
    ];

    protected $casts = [
        'sarcasm' => 'boolean',
        'topics' => 'array',
        'confidence' => 'float',
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'raw_json' => 'array',
        'classified_at' => 'datetime',
    ];

    public function mention(): BelongsTo
    {
        return $this->belongsTo(PublicPulseMention::class, 'mention_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
