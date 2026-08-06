<?php

namespace App\Http\Middleware;

use App\Models\Candidate;
use App\Services\Audit\AuditService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AuditMutatingRequests
{
    public function __construct(private AuditService $audits) {}

    public function handle(Request $request, Closure $next): Response
    {
        $actor = $request->user();
        $submittedFields = array_values(array_diff(
            array_unique(array_merge(
                array_keys($request->request->all()),
                array_keys($request->files->all())
            )),
            ['password', 'password_confirmation', '_token', '_method']
        ));

        $response = $next($request);

        $routeName = $request->route()?->getName();
        $isMutation = in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true);
        $isSensitiveRead = $request->isMethod('GET') && is_string($routeName) && Str::contains($routeName, ['export', 'download', 'document']);
        if (! $actor || (! $isMutation && ! $isSensitiveRead)) {
            return $response;
        }
        if (! $routeName || str_starts_with($routeName, 'audits.') || str_starts_with($routeName, 'aspirant.audits.')) {
            return $response;
        }

        $candidate = $request->route('candidate');
        $candidateId = $candidate instanceof Candidate
            ? (int) $candidate->id
            : (int) ($request->session()->get('active_candidate_id') ?: 0);

        $successful = $response->getStatusCode() < 400;
        $this->audits->record('operation.'.$routeName, Str::headline(str_replace('.', ' ', $routeName)).'.', [
            'actor' => $actor,
            'candidate_id' => $candidateId ?: null,
            'auditable' => $candidate instanceof Candidate ? $candidate : null,
            'module' => Str::before($routeName, '.'),
            'status' => $successful ? 'success' : 'failure',
            'metadata' => [
                'method' => $request->method(),
                'status_code' => $response->getStatusCode(),
                'submitted_fields' => $submittedFields,
            ],
        ]);

        return $response;
    }
}
