<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateTokenPurchase extends Model
{
    protected $fillable = [
        'candidate_id',
        'user_id',
        'candidate_token_package_id',
        'payment_method_id',
        'provider',
        'checkout_reference',
        'package_name',
        'token_amount',
        'price',
        'currency',
        'payment_reference',
        'gateway_transaction_code',
        'gateway_status',
        'gateway_response',
        'callback_received_at',
        'status',
        'credited_at',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'callback_received_at' => 'datetime',
        'credited_at' => 'datetime',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(CandidateTokenPackage::class, 'candidate_token_package_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
