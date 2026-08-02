<?php

namespace App\Jobs;

use App\Services\SupportGroups\SupportContactImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImportCandidateSupportContacts implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 300;

    public function __construct(
        private string $storedPath,
        private string $extension,
        private int $candidateId,
        private int $userId,
        private ?int $defaultGroupId = null
    ) {}

    public function handle(SupportContactImportService $importer): void
    {
        $disk = Storage::disk('local');

        if (! $disk->exists($this->storedPath)) {
            Log::warning('Support contacts import skipped because the uploaded file was not found.', [
                'candidate_id' => $this->candidateId,
                'user_id' => $this->userId,
                'path' => $this->storedPath,
            ]);

            return;
        }

        try {
            $result = $importer->import(
                $disk->path($this->storedPath),
                $this->extension,
                $this->candidateId,
                $this->userId,
                $this->defaultGroupId
            );

            Log::info('Support contacts import completed.', [
                'candidate_id' => $this->candidateId,
                'user_id' => $this->userId,
                'imported' => $result['imported'],
                'skipped' => $result['skipped'],
                'errors' => $result['errors'],
            ]);
        } catch (Throwable $exception) {
            Log::error('Support contacts import failed.', [
                'candidate_id' => $this->candidateId,
                'user_id' => $this->userId,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        } finally {
            $disk->delete($this->storedPath);
        }
    }
}