<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\CandidateBulkSmsContactRepositoryInterface;
use App\Jobs\ImportCandidateSupportContacts;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CandidateBulkSmsContactService
{
    public function __construct(private CandidateBulkSmsContactRepositoryInterface $contacts) {}

    public function classifications(): Collection
    {
        return $this->contacts->activeClassifications();
    }

    public function recipientCount(Candidate $candidate, ?int $classificationId = null): int
    {
        return $this->contacts->countForCandidate($candidate->id, $classificationId);
    }

    public function countsByClassification(Candidate $candidate): Collection
    {
        return $this->contacts->countsByClassification($candidate->id);
    }
    public function queueImport(Candidate $candidate, User $user, UploadedFile $file, int $classificationId): void
    {
        $extension = Str::lower($file->getClientOriginalExtension());
        $storedPath = $file->storeAs(
            'bulk-sms-contact-imports/' . $candidate->id,
            Str::uuid() . '.' . $extension
        );

        ImportCandidateSupportContacts::dispatch(
            $storedPath,
            $extension,
            $candidate->id,
            $user->id,
            $classificationId
        );
    }
}