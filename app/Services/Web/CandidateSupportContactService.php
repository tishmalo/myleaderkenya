<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\CandidateSupportContactRepositoryInterface;
use App\Jobs\ImportCandidateSupportContacts;
use App\Models\Candidate;
use App\Models\CandidateSupportContact;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class CandidateSupportContactService
{
    public function __construct(private CandidateSupportContactRepositoryInterface $contacts) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public function create(Candidate $candidate, User $user, array $validated): CandidateSupportContact
    {
        return $this->contacts->create($validated + [
            'candidate_id' => $candidate->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(CandidateSupportContact $contact, ?Candidate $candidate, User $user, array $validated): void
    {
        $this->guardOwnership($contact, $candidate);

        $this->contacts->update($contact, $validated + ['updated_by' => $user->id]);
    }

    public function delete(CandidateSupportContact $contact, ?Candidate $candidate): void
    {
        $this->guardOwnership($contact, $candidate);

        $this->contacts->delete($contact);
    }

    public function queueImport(Candidate $candidate, User $user, UploadedFile $file, ?int $defaultGroupId): void
    {
        $extension = Str::lower($file->getClientOriginalExtension());

        $storedPath = $file->storeAs(
            'support-contact-imports/' . $candidate->id,
            Str::uuid() . '.' . $extension
        );

        ImportCandidateSupportContacts::dispatch(
            $storedPath,
            $extension,
            $candidate->id,
            $user->id,
            $defaultGroupId
        );
    }

    private function guardOwnership(CandidateSupportContact $contact, ?Candidate $candidate): void
    {
        abort_unless($candidate && (int) $contact->candidate_id === (int) $candidate->id, 403);
    }
}
