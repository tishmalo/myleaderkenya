<?php

namespace App\Services\SupportGroups;

use App\Models\Candidate;
use App\Models\SupportGroupType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class SupportContactImportService
{
    public function import(string $path, string $extension, int $candidateId, int $userId, ?int $defaultGroupId = null): array
    {
        $candidate = Candidate::find($candidateId);
        if (! $candidate) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => ['Candidate was not found.']];
        }

        $rows = $this->supportContactImportRows($path, $extension);
        $groupLookup = SupportGroupType::active()->ordered()->get()->flatMap(fn (SupportGroupType $type) => [
            Str::lower($type->name) => $type->id,
            Str::lower($type->slug) => $type->id,
        ]);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        DB::transaction(function () use ($rows, $candidate, $userId, $defaultGroupId, $groupLookup, &$imported, &$skipped, &$errors): void {
            foreach ($rows as $line => $row) {
                $name = trim((string) ($row['name'] ?? ''));
                $email = trim((string) ($row['email'] ?? ''));
                $phone = trim((string) ($row['phone'] ?? ''));
                $groupValue = trim((string) ($row['group'] ?? ''));
                $groupId = $groupValue !== '' ? $groupLookup->get(Str::lower($groupValue)) : $defaultGroupId;

                if ($name === '' && $email === '' && $phone === '' && $groupValue === '') {
                    continue;
                }

                if (! $groupId || $name === '' || ($email === '' && $phone === '')) {
                    $skipped++;
                    if (count($errors) < 5) {
                        $errors[] = "Row {$line}: add a valid group, name, and email or phone.";
                    }
                    continue;
                }

                if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $skipped++;
                    if (count($errors) < 5) {
                        $errors[] = "Row {$line}: email is invalid.";
                    }
                    continue;
                }

                if ($phone !== '' && ! preg_match('/^[0-9+() .-]+$/', $phone)) {
                    $skipped++;
                    if (count($errors) < 5) {
                        $errors[] = "Row {$line}: phone contains unsupported characters.";
                    }
                    continue;
                }

                $candidate->supportContacts()->create([
                    'support_group_type_id' => $groupId,
                    'name' => Str::limit($name, 255, ''),
                    'email' => $email !== '' ? Str::limit($email, 255, '') : null,
                    'phone' => $phone !== '' ? Str::limit($phone, 50, '') : null,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);

                $imported++;
            }
        });

        return compact('imported', 'skipped', 'errors');
    }

    private function supportContactImportRows(string $path, string $extension): array
    {
        $extension = Str::lower($extension);

        if (in_array($extension, ['csv', 'txt'], true)) {
            return $this->csvSupportContactRows($path);
        }

        if ($extension === 'xlsx') {
            return $this->xlsxSupportContactRows($path);
        }

        throw ValidationException::withMessages([
            'contacts_file' => 'Upload an XLSX or CSV file.',
        ]);
    }

    private function csvSupportContactRows(string $path): array
    {
        $handle = fopen($path, 'rb');
        if (! $handle) {
            throw ValidationException::withMessages([
                'contacts_file' => 'The uploaded file could not be read.',
            ]);
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $this->normalizeSupportContactRows($rows);
    }

    private function xlsxSupportContactRows(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw ValidationException::withMessages([
                'contacts_file' => 'XLSX import needs the PHP zip extension. Upload CSV instead or enable zip on the server.',
            ]);
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw ValidationException::withMessages([
                'contacts_file' => 'The XLSX file could not be opened.',
            ]);
        }

        $sharedStrings = $this->xlsxSharedStrings($zip);
        $worksheetPath = $this->xlsxFirstWorksheetPath($zip);
        $sheetXml = $worksheetPath ? $zip->getFromName($worksheetPath) : false;
        $zip->close();

        if ($sheetXml === false) {
            throw ValidationException::withMessages([
                'contacts_file' => 'The XLSX file does not contain a readable worksheet.',
            ]);
        }

        $sheet = simplexml_load_string($sheetXml);
        if (! $sheet || ! isset($sheet->sheetData->row)) {
            return [];
        }

        $rows = [];
        foreach ($sheet->sheetData->row as $xmlRow) {
            $cells = [];
            foreach ($xmlRow->c as $cell) {
                $reference = (string) $cell['r'];
                preg_match('/^[A-Z]+/', $reference, $match);
                $index = $this->xlsxColumnIndex($match[0] ?? '') - 1;
                if ($index < 0) {
                    $index = count($cells);
                }

                $type = (string) $cell['t'];
                if ($type === 's') {
                    $value = $sharedStrings[(int) $cell->v] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = trim((string) ($cell->is->t ?? ''));
                } else {
                    $value = trim((string) ($cell->v ?? ''));
                }

                $cells[$index] = $value;
            }

            if ($cells !== []) {
                ksort($cells);
                $rows[] = array_values($cells);
            }
        }

        return $this->normalizeSupportContactRows($rows);
    }

    private function xlsxSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }

        $strings = [];
        $shared = simplexml_load_string($xml);
        if (! $shared) {
            return [];
        }

        foreach ($shared->si as $item) {
            if (isset($item->t)) {
                $strings[] = trim((string) $item->t);
                continue;
            }

            $text = '';
            foreach ($item->r as $run) {
                $text .= (string) ($run->t ?? '');
            }
            $strings[] = trim($text);
        }

        return $strings;
    }

    private function xlsxFirstWorksheetPath(ZipArchive $zip): ?string
    {
        if ($zip->locateName('xl/worksheets/sheet1.xml') !== false) {
            return 'xl/worksheets/sheet1.xml';
        }

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = $zip->getNameIndex($index);
            if (str_starts_with($name, 'xl/worksheets/') && str_ends_with($name, '.xml')) {
                return $name;
            }
        }

        return null;
    }

    private function normalizeSupportContactRows(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        $headers = array_shift($rows);
        $map = [];
        foreach ($headers as $index => $header) {
            $key = $this->supportContactHeaderKey((string) $header);
            if ($key) {
                $map[$index] = $key;
            }
        }

        if (! in_array('name', $map, true)) {
            throw ValidationException::withMessages([
                'contacts_file' => 'The file must include a header row with at least name, plus email or phone. Optional: group.',
            ]);
        }

        $normalized = [];
        foreach ($rows as $rowIndex => $row) {
            $line = $rowIndex + 2;
            foreach ($map as $columnIndex => $key) {
                $normalized[$line][$key] = trim((string) ($row[$columnIndex] ?? ''));
            }
        }

        return $normalized;
    }

    private function supportContactHeaderKey(string $header): ?string
    {
        $normalized = preg_replace('/[^a-z0-9]+/', '', Str::lower(trim($header)));

        return match ($normalized) {
            'group', 'groups', 'supportgroup', 'supportgroups', 'supportgrouptype' => 'group',
            'name', 'contact', 'contactname', 'fullname', 'fullnames' => 'name',
            'email', 'mail', 'emailaddress' => 'email',
            'phone', 'mobile', 'phonenumber', 'telephone', 'tel' => 'phone',
            default => null,
        };
    }

    private function xlsxColumnIndex(string $letters): int
    {
        $index = 0;
        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index;
    }
}