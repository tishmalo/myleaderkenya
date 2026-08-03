<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model implements AuditableContract
{
    use AuditsChanges;
    protected $fillable = ['key', 'value'];
}
