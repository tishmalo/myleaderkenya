<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'nick_name', 'phone', 'email', 'position_id', 'political_party_id', 'bloc_id', 'user_id',
        'profile_picture', 'cover_photo', 'featured', 'approval_status', 'about', 'country', 'county', 'constituency', 'ward',
        'claim_token_hash', 'claim_token_expires_at', 'claim_sent_at', 'claimed_at',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'claim_token_expires_at' => 'datetime',
        'claim_sent_at' => 'datetime',
        'claimed_at' => 'datetime',
    ];

    public function getEmailAttribute($value): ?string
    {
        return $this->decryptNullableString($value);
    }

    public function setEmailAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['email'] = null;
            return;
        }

        $email = Str::lower(trim((string) $value));
        $this->attributes['email'] = Crypt::encryptString($email);
    }

    public function getPhoneAttribute($value): ?string
    {
        return $this->decryptNullableString($value);
    }

    public function setPhoneAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['phone'] = null;
            return;
        }

        $phone = trim((string) $value);
        $this->attributes['phone'] = Crypt::encryptString($phone);
    }

    public function maskedEmail(): ?string
    {
        return $this->maskEmail($this->email);
    }

    public function maskedPhone(): ?string
    {
        return $this->maskPhone($this->phone);
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

    private function maskEmail(?string $email): ?string
    {
        if ($email === null || $email === '') {
            return null;
        }

        $email = trim($email);
        if (! str_contains($email, '@')) {
            return $this->maskString($email, 2, 2);
        }

        [$local, $domain] = explode('@', $email, 2);
        $domainParts = explode('.', $domain);
        $extension = count($domainParts) > 1 ? array_pop($domainParts) : null;
        $domainName = implode('.', $domainParts) ?: $domain;

        $masked = $this->maskString($local, 2, 1) . '@' . $this->maskString($domainName, 1, 0);

        return $extension ? $masked . '.' . $extension : $masked;
    }

    private function maskPhone(?string $phone): ?string
    {
        if ($phone === null || $phone === '') {
            return null;
        }

        $phone = trim($phone);
        $digits = preg_replace('/\D+/', '', $phone) ?: '';

        if (strlen($digits) < 7) {
            return $this->maskString($phone, 2, 1);
        }

        return substr($digits, 0, 3) . str_repeat('*', max(strlen($digits) - 5, 3)) . substr($digits, -2);
    }

    private function maskString(string $value, int $visibleStart = 1, int $visibleEnd = 1): string
    {
        $length = strlen($value);

        if ($length <= $visibleStart + $visibleEnd) {
            return str_repeat('*', max($length, 3));
        }

        return substr($value, 0, $visibleStart)
            . str_repeat('*', max($length - $visibleStart - $visibleEnd, 3))
            . ($visibleEnd > 0 ? substr($value, -$visibleEnd) : '');
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function politicalParty()
    {
        return $this->belongsTo(PoliticalParty::class);
    }

    public function bloc()
    {
        return $this->belongsTo(Bloc::class);
    }

    public function smsSetting(): HasOne
    {
        return $this->hasOne(CandidateSmsSetting::class);
    }

    public function smsMessages(): HasMany
    {
        return $this->hasMany(CandidateSmsMessage::class);
    }

    public function relatedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'candidate_user_relationships')
            ->withPivot('relationship')
            ->withTimestamps();
    }
}
