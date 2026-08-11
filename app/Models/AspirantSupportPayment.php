<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class AspirantSupportPayment extends Model implements AuditableContract
{
    use AuditsChanges;

    protected $fillable = [
        'user_id', 'candidate_id', 'candidate_token_package_id', 'supporter_name',
        'supporter_email', 'supporter_phone', 'message', 'provider', 'checkout_reference',
        'gross_amount', 'platform_fee_rate', 'platform_fee_amount', 'aspirant_amount',
        'currency', 'payment_reference', 'gateway_transaction_code', 'gateway_status',
        'gateway_response', 'callback_received_at', 'status', 'paid_at', 'aspirant_reply',
        'replied_at', 'replied_by',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'platform_fee_rate' => 'decimal:2',
        'platform_fee_amount' => 'decimal:2',
        'aspirant_amount' => 'decimal:2',
        'gateway_response' => 'array',
        'callback_received_at' => 'datetime',
        'paid_at' => 'datetime',
        'replied_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function candidate(): BelongsTo { return $this->belongsTo(Candidate::class); }
    public function package(): BelongsTo { return $this->belongsTo(CandidateTokenPackage::class, 'candidate_token_package_id'); }
    public function repliedBy(): BelongsTo { return $this->belongsTo(User::class, 'replied_by'); }
}
