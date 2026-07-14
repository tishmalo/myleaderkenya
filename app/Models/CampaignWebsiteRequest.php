<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignWebsiteRequest extends Model
{
    protected $fillable = [
        'candidate_id',
        'user_id',
        'candidate_name',
        'phone',
        'email',
        'preferred_domain',
        'website_type',
        'reference_url',
        'notes',
        'status',
        'admin_notes',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
