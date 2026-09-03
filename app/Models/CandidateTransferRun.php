<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;

class CandidateTransferRun extends Model implements AuditableContract
{
    use AuditsChanges;

    protected $fillable = [
        'type', 'status', 'requested_by', 'source_path', 'result_path', 'download_name',
        'filters', 'imported_count', 'linked_count', 'skipped_count', 'exported_count',
        'errors', 'error_message', 'started_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'errors' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}