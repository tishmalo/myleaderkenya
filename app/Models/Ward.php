<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ward extends Model implements AuditableContract
{
    use AuditsChanges;
    use HasFactory;

    protected $fillable = [
        'name',
        'constituency_id',
        'population',
        'registered_voters',
        'image',
    ];

    // Relationship: Ward belongs to one Constituency
    public function constituency()
    {
        return $this->belongsTo(Constituency::class);
    }

    public function pollingStations()
{
    return $this->hasMany(PollingStation::class, 'ward', 'name');
}
//     public function pollingStations()
// {
//     return $this->hasMany(\App\Models\PollingStation::class);
// }
}
