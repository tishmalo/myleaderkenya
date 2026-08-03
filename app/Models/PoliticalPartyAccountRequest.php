<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;

class PoliticalPartyAccountRequest extends Model implements AuditableContract
{
    use AuditsChanges;
    protected $guarded = [];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function politicalParty()
    {
        return $this->belongsTo(PoliticalParty::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
