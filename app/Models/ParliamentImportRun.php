<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParliamentImportRun extends Model
{
    protected $fillable = ['import_key', 'status', 'members_received', 'members_saved', 'failure_code', 'started_at', 'completed_at', 'requested_by'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'completed_at' => 'datetime'];
    }
}