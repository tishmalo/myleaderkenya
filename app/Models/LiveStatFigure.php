<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveStatFigure extends Model implements AuditableContract
{
    use AuditsChanges;
    use HasFactory;

    public const METRICS = [
        'confirmed_voters' => 'Confirmed Voters',
        'total_users' => 'Tuko Kadi Members',
        'total_messages' => 'Community Messages',
        'stations_count' => 'Polling Stations',
    ];

    protected $fillable = [
        'metric_key',
        'label',
        'value',
        'source',
        'batch_id',
        'batch_name',
        'notes',
        'active',
    ];

    protected $casts = [
        'value' => 'integer',
        'active' => 'boolean',
    ];
}
