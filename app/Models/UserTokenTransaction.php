<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class UserTokenTransaction extends Model implements AuditableContract
{
    use AuditsChanges;
    protected $fillable = ['user_token_wallet_id','user_id','candidate_id','user_token_purchase_id','tokenable_type','tokenable_id','type','status','action_key','action_label','amount','balance_before','balance_after','metadata','finalized_at'];
    protected $casts = ['metadata'=>'array','finalized_at'=>'datetime'];
    public function wallet(): BelongsTo { return $this->belongsTo(UserTokenWallet::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function candidate(): BelongsTo { return $this->belongsTo(Candidate::class); }
    public function tokenable(): MorphTo { return $this->morphTo(); }
}