<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Poll extends Model implements AuditableContract
{
    use AuditsChanges;

    public const TYPE_WORDS = 'words';

    public const TYPE_POLITICAL = 'political';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'question',
        'slug',
        'poll_type',
        'status',
        'starts_at',
        'ends_at',
        'reveal_results',
        'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'reveal_results' => 'boolean',
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

    public function totalVotes(): int
    {
        return $this->votes_count ?? $this->votes()->count();
    }

    /**
     * Vote tallies per option, ordered as the options render. Kept in one
     * place so the homepage and any results view cannot drift apart.
     *
     * @return Collection<int, array{option: PollOption, votes: int, percent: int}>
     */
    public function results(): Collection
    {
        // Wrapped so map()/values() stay a base Collection: Eloquent's
        // values() downcasts, which would break the declared return type.
        $options = collect($this->options()->get());

        if ($options->isEmpty()) {
            return collect();
        }

        $counts = $this->votes()
            ->selectRaw('poll_option_id, COUNT(*) as aggregate')
            ->groupBy('poll_option_id')
            ->pluck('aggregate', 'poll_option_id');

        $total = (int) $counts->sum();

        return $options->map(fn (PollOption $option) => [
            'option' => $option,
            'votes' => (int) ($counts[$option->id] ?? 0),
            'percent' => $total > 0
                ? (int) round(((int) ($counts[$option->id] ?? 0)) / $total * 100)
                : 0,
        ])->values();
    }

    public function hasVotedBy(?int $userId): bool
    {
        if ($userId === null) {
            return false;
        }

        return $this->votes()->where('user_id', $userId)->exists();
    }
}
