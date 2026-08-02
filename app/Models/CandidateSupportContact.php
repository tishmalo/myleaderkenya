<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class CandidateSupportContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'support_group_type_id',
        'name',
        'email',
        'phone',
        'created_by',
        'updated_by',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function groupType(): BelongsTo
    {
        return $this->belongsTo(SupportGroupType::class, 'support_group_type_id');
    }

    public function getEmailAttribute($value): ?string
    {
        return $this->decryptNullableString($value);
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = $this->encryptNullableString($this->normalizeEmail($value));
    }

    public function getPhoneAttribute($value): ?string
    {
        return $this->decryptNullableString($value);
    }

    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = $this->encryptNullableString($value);
    }

    private function encryptNullableString($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Crypt::encryptString(trim((string) $value));
    }

    private function decryptNullableString($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return (string) $value;
        }
    }

    private function normalizeEmail($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Str::lower(trim((string) $value));
    }
}
