<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use App\Models\Concerns\EncryptsPiiAttributes;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Candidate extends Model implements AuditableContract
{
    use AuditsChanges;
    use EncryptsPiiAttributes, HasFactory;

    protected $fillable = [
        'name', 'slug', 'nick_name', 'phone', 'email', 'position_id', 'political_party_id', 'bloc_id', 'user_id',
        'profile_picture', 'cover_photo', 'campaign_poster', 'campaign_video', 'campaign_video_url', 'campaign_song_url', 'campaign_skiza_audio',
        'phone_1', 'phone_2', 'email_1', 'email_2', 'featured', 'approval_status', 'about', 'country', 'county', 'constituency', 'ward',
        'facebook_url', 'x_url', 'instagram_url', 'tiktok_url', 'youtube_url', 'whatsapp_group_url',
        'claim_token_hash', 'claim_token_expires_at', 'claim_sent_at', 'claimed_at',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'claim_token_expires_at' => 'datetime',
        'claim_sent_at' => 'datetime',
        'claimed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Candidate $candidate): void {
            $candidate->ensureCandidateSlug();
        });
    }

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


    public function getPhone1Attribute($value): ?string
    {
        return $this->decryptNullableString($value);
    }

    public function setPhone1Attribute($value): void
    {
        $this->attributes['phone_1'] = $this->encryptNullableString($value);
    }

    public function getPhone2Attribute($value): ?string
    {
        return $this->decryptNullableString($value);
    }

    public function setPhone2Attribute($value): void
    {
        $this->attributes['phone_2'] = $this->encryptNullableString($value);
    }

    public function getEmail1Attribute($value): ?string
    {
        return $this->decryptNullableString($value);
    }

    public function setEmail1Attribute($value): void
    {
        $this->attributes['email_1'] = $this->encryptNullableString($this->normalizeEmail($value));
    }

    public function getEmail2Attribute($value): ?string
    {
        return $this->decryptNullableString($value);
    }

    public function setEmail2Attribute($value): void
    {
        $this->attributes['email_2'] = $this->encryptNullableString($this->normalizeEmail($value));
    }
    public function maskedEmail(): ?string
    {
        return $this->maskEmail($this->email);
    }

    public function maskedPhone(): ?string
    {
        return $this->maskPhone($this->phone);
    }

    public function getRouteKey()
    {
        return $this->slug ?: $this->getKey();
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $query = $this->newQuery();

        if (is_numeric($value)) {
            return $query->where($this->getKeyName(), $value)->first();
        }

        if (Schema::hasColumn($this->getTable(), 'slug')) {
            return $query->where('slug', $value)->first();
        }

        return null;
    }

    public function getDisplayAreaAttribute(): ?string
    {
        $positionName = Str::lower((string) ($this->position?->name ?? ''));

        if ($this->isMcaPosition($positionName) && $this->ward) {
            return $this->ward;
        }

        if ($this->isMpPosition($positionName) && $this->constituency) {
            return $this->constituency;
        }

        return $this->county ?: ($this->country ?: null);
    }

    private function encryptNullableString($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Crypt::encryptString(trim((string) $value));
    }

    private function normalizeEmail($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Str::lower(trim((string) $value));
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

    private function ensureCandidateSlug(): void
    {
        if (! Schema::hasColumn($this->getTable(), 'slug')) {
            return;
        }

        if (! $this->isDirty('name') && filled($this->slug)) {
            return;
        }

        $baseSlug = Str::slug((string) ($this->slug ?: $this->name));
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'aspirant';
        $slug = $baseSlug;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($this->exists, fn ($query) => $query->whereKeyNot($this->getKey()))
            ->exists()) {
            $slug = $baseSlug . '-' . $suffix++;
        }

        $this->slug = $slug;
    }

    private function isMpPosition(string $positionName): bool
    {
        return $positionName === 'mp'
            || str_contains($positionName, 'member of parliament')
            || str_starts_with($positionName, 'mp ');
    }

    private function isMcaPosition(string $positionName): bool
    {
        return $positionName === 'mca'
            || str_contains($positionName, 'member of county assembly');
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function tokenWallet(): HasOne
    {
        return $this->hasOne(CandidateTokenWallet::class);
    }

    public function tokenTransactions(): HasMany
    {
        return $this->hasMany(CandidateTokenTransaction::class);
    }

    public function tokenPurchases(): HasMany
    {
        return $this->hasMany(CandidateTokenPurchase::class);
    }

    public function smsBalanceRequests(): HasMany
    {
        return $this->hasMany(CandidateSmsBalanceRequest::class);
    }

    public function supportContacts(): HasMany
    {
        return $this->hasMany(CandidateSupportContact::class);
    }

    public function parliamentMember(): HasOne
    {
        return $this->hasOne(ParliamentMember::class);
    }
    public function campaignPriorities(): HasMany
    {
        return $this->hasMany(CandidateCampaignPriority::class);
    }

    public function claimRequests(): HasMany
    {
        return $this->hasMany(CandidateClaimRequest::class);
    }

    public function relatedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'candidate_user_relationships')
            ->withPivot('relationship', 'dashboard_access_enabled')
            ->withTimestamps();
    }
}
