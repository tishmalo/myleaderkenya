<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class SeoPage extends Model implements AuditableContract
{
    use AuditsChanges;

    protected $fillable = [
        'route_name',
        'title',
        'description',
        'canonical',
    ];
}
