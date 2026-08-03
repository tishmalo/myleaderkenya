<?php

namespace App\Models\Concerns;

use OwenIt\Auditing\Auditable as AuditableTrait;

trait AuditsChanges
{
    use AuditableTrait;

    protected array $auditExclude = [
        'password', 'remember_token', 'email_hash', 'phone_hash', 'id_number_hash',
        'claim_token_hash', 'api_token', 'token', 'secret', 'provider_response',
    ];

    public function transformAudit(array $data): array
    {
        $data['old_values'] = app(\App\Services\Audit\AuditRedactor::class)->redact($data['old_values'] ?? []);
        $data['new_values'] = app(\App\Services\Audit\AuditRedactor::class)->redact($data['new_values'] ?? []);
        $candidateId = $this instanceof \App\Models\Candidate ? $this->getKey() : $this->getAttribute('candidate_id');
        $data['candidate_id'] = $candidateId ?: null;
        $data['module'] = class_basename($this);
        $data['status'] = 'success';
        $data['summary'] = class_basename($this).' '.($data['event'] ?? 'changed');
        $data['metadata'] = [];

        return $data;
    }
}

