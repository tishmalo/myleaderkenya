<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class UserTokenWallet extends Model implements AuditableContract
{
    use AuditsChanges;
    protected $fillable = ['user_id', 'balance'];
    protected $casts = ['balance' => 'integer'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function transactions(): HasMany { return $this->hasMany(UserTokenTransaction::class); }
}