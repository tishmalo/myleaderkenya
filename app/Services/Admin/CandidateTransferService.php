<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\CandidateTransferRunRepositoryInterface;
use App\Jobs\ExportCandidates;
use App\Jobs\ImportCandidates;
use App\Models\CandidateTransferRun;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Queues and reports on the background CSV import and export runs.
 */
class CandidateTransferService
{
    private const TEMPLATE_COLUMNS = [
        'name', 'nick_name', 'phone', 'email', 'political_party',
        'position', 'county', 'constituency', 'ward', 'about',
    ];

    private const TEMPLATE_SAMPLE_ROW = [
        'Jane Doe', 'JD', '0712345678', 'jane@example.com', 'UDA',
        'Governor', 'Nairobi', '', '', 'About text',
    ];

    public function __construct(
        private CandidateTransferRunRepositoryInterface $runs
    ) {}

    public function recentRuns(int $limit = 10): Collection
    {
        return $this->runs->recent($limit);
    }

    public function queueImport(UploadedFile $file, int $requestedBy): CandidateTransferRun
    {
        $run = $this->runs->create([
            'type' => 'import',
            'status' => 'pending',
            'source_path' => $file->store('imports'),
            'requested_by' => $requestedBy,
        ]);

        ImportCandidates::dispatch($run->id);

        return $run;
    }

    public function queueExport(array $filters, int $requestedBy): CandidateTransferRun
    {
        $run = $this->runs->create([
            'type' => 'export',
            'status' => 'pending',
            'filters' => $filters,
            'requested_by' => $requestedBy,
        ]);

        ExportCandidates::dispatch($run->id);

        return $run;
    }

    public function downloadResponse(CandidateTransferRun $run): StreamedResponse|BinaryFileResponse
    {
        abort_unless($this->isDownloadable($run), 404);

        return Storage::disk('local')->download($run->result_path, $run->download_name ?? 'candidates.csv');
    }

    /**
     * @return array<string, mixed>
     */
    public function statusPayload(CandidateTransferRun $run): array
    {
        return [
            'id' => $run->id,
            'type' => $run->type,
            'status' => $run->status,
            'imported_count' => $run->imported_count,
            'linked_count' => $run->linked_count,
            'skipped_count' => $run->skipped_count,
            'exported_count' => $run->exported_count,
            'error_message' => $run->error_message,
            'errors' => $run->errors,
            'download_url' => $this->isDownloadable($run)
                ? route('candidates.export.download', $run)
                : null,
        ];
    }

    public function importTemplateResponse(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, self::TEMPLATE_COLUMNS);
            fputcsv($out, self::TEMPLATE_SAMPLE_ROW);
            fclose($out);
        }, 'candidates_import_template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function isDownloadable(CandidateTransferRun $run): bool
    {
        return $run->type === 'export'
            && $run->status === 'complete'
            && filled($run->result_path);
    }
}
