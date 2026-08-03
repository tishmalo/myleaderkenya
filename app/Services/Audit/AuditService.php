<?php

namespace App\Services\Audit;

use App\Contracts\Repositories\Audit\AuditRepositoryInterface;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditService
{
    public function __construct(private AuditRepositoryInterface $audits, private AuditRedactor $redactor) {}

    public function record(string $event, string $summary, array $context = []): void
    {
        if (! \Schema::hasTable('audits')) return;
        $actor = $context['actor'] ?? auth()->user();
        $candidateId = $context['candidate_id'] ?? $this->candidateId($context['auditable'] ?? null);
        $auditable = $context['auditable'] ?? null;
        $correlationId = $context['correlation_id'] ?? (app()->bound('request') ? request()->header('X-Request-ID') : null);
        if (! is_string($correlationId) || ! Str::isUuid($correlationId)) $correlationId = (string) Str::uuid();
        $this->audits->create([
            'user_type' => $actor ? $actor->getMorphClass() : null,
            'user_id' => $actor?->getKey(),
            'event' => $event,
            'auditable_type' => $auditable?->getMorphClass() ?? Candidate::class,
            'auditable_id' => $auditable?->getKey() ?? ($candidateId ?: 0),
            'old_values' => $this->redactor->redact($context['old_values'] ?? []),
            'new_values' => $this->redactor->redact($context['new_values'] ?? []),
            'url' => app()->runningInConsole() ? null : request()->fullUrl(),
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
            'user_agent' => app()->runningInConsole() ? null : request()->userAgent(),
            'tags' => $context['tags'] ?? null,
            'candidate_id' => $candidateId,
            'module' => $context['module'] ?? Str::before($event, '.'),
            'summary' => $summary,
            'status' => $context['status'] ?? 'success',
            'correlation_id' => $correlationId,
            'batch_id' => $context['batch_id'] ?? null,
            'metadata' => $this->redactor->redact($context['metadata'] ?? []),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function candidateId(?Model $model): ?int
    {
        if (! $model) return null;
        if ($model instanceof Candidate) return (int) $model->getKey();
        $id = $model->getAttribute('candidate_id');
        return $id ? (int) $id : null;
    }

    public function actorLabel(?User $actor, bool $aspirantView): string
    {
        if (! $actor) return 'System';
        if ($aspirantView && $actor->isAdmin()) return 'Administrator';
        return $actor->name ?: $actor->roleLabel();
    }
}

