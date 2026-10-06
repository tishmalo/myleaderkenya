<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Poll extends Model implements AuditableContract
{
    use AuditsChanges;

    public const TYPE_WORDS = 'words';

    public const TYPE_POLITICAL = 'political';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_CLOSED = 'closed';

    public const AUDIENCE_NATIONAL = 'national';

    public const AUDIENCE_MEMBERS = 'members';

    public const AUDIENCE_COUNTY = 'county';

    public const AUDIENCE_CONSTITUENCY = 'constituency';

    public const AUDIENCE_WARD = 'ward';

    protected $fillable = [
        'question',
        'slug',
        'poll_type',
        'status',
        'starts_at',
        'ends_at',
        'reveal_results',
        'show_results_to_voters',
        'created_by',
        'audience_scope',
        'audience_county',
        'audience_constituency',
        'audience_ward',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'reveal_results' => 'boolean',
        'show_results_to_voters' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class)->orderBy('display_order');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PollComment::class);
    }

    public function approvedComments(): HasMany
    {
        return $this->comments()->where('status', 'approved')->latest();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeOpenForVoting(Builder $query): Builder
    {
        $now = now();

        return $query->active()
            ->where(fn (Builder $inner) => $inner->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where('ends_at', '>', $now);
    }

    /**
     * Whether the poll is currently accepting votes, from the public's view.
     */
    public function isOpenForVoting(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        $now = now();

        return ($this->starts_at === null || $this->starts_at->lessThanOrEqualTo($now))
            && $this->ends_at->greaterThan($now);
    }

    /**
     * Results stay hidden until the deadline passes, and only then if the
     * author chose to reveal them. Admins bypass this via the admin views.
     */
    public function hasPublicResults(): bool
    {
        return $this->reveal_results && $this->ends_at->isPast();
    }
}
