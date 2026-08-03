<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model implements AuditableContract
{
    use AuditsChanges;
    protected $fillable = [
        'name',
        'type',
        'account_number',
        'account_name',
        'phone_number',
        'bank_name',
        'branch',
        'instructions',
        'is_active',
    ];
}
