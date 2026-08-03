<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;

class ParliamentImportRun extends Model implements AuditableContract
{
    use AuditsChanges;
    protected $fillable = ['import_key', 'status', 'members_received', 'members_saved', 'failure_code', 'started_at', 'completed_at', 'requested_by'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'completed_at' => 'datetime'];
    }
}
