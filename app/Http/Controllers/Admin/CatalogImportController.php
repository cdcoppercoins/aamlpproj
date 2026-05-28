<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PlateCsvImporter;
use App\Support\PlateCsvColumns;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CatalogImportController extends Controller
{
    public function create(Request $request): View
    {
        return view('admin.catalog.import', [
            'prefillSetCode' => trim((string) $request->query('set_code', '')),
            'prefillSetName' => trim((string) $request->query('set_name', '')),
            'prefillCompany' => trim((string) $request->query('company', '')),
            'prefillYear' => trim((string) $request->query('year', '')),
            'columnHeaders' => PlateCsvColumns::HEADERS,
        ]);
    }

    public function downloadTemplate(Request $request, PlateCsvImporter $importer): Response
    {
        $setCode = trim((string) $request->query('set_code', 'newset'));
        $setName = trim((string) $request->query('set_name', 'New Set Name'));

        $prefill = [
            'set_code' => $setCode !== '' ? $setCode : 'newset',
            'set_name' => $setName !== '' ? $setName : 'New Set Name',
            'company' => trim((string) $request->query('company', '')),
            'year' => trim((string) $request->query('year', '')),
        ];

        $filename = 'catalog-set-' . Str::slug($prefill['set_code'], '-') . '-template.csv';
        $csv = $importer->renderTemplateCsv($prefill);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function store(Request $request, PlateCsvImporter $importer): RedirectResponse
    {
        $validated = $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:20480'],
            'set_code' => ['nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9._-]+$/'],
        ], [], [
            'csv_file' => 'CSV file',
            'set_code' => 'set code',
        ]);

        $expectedSetCode = isset($validated['set_code']) ? trim($validated['set_code']) : null;
        if ($expectedSetCode === '') {
            $expectedSetCode = null;
        }

        $result = $importer->importFromUploadedFile($request->file('csv_file'), $expectedSetCode);

        $message = "Import finished. Added {$result['imported']} plates";
        $message .= $result['skipped'] > 0 ? ", skipped {$result['skipped']} rows." : '.';

        if (! empty($result['set_codes'])) {
            $message .= ' Set folder(s): ' . implode(', ', $result['set_codes']) . '.';
        }

        if (! empty($result['errors'])) {
            session()->flash('error', implode(' ', array_slice($result['errors'], 0, 5)));
        }

        $redirect = redirect()->route('admin.catalog.import.create');
        if ($expectedSetCode) {
            $redirect = redirect()->route('admin.catalog.sets.show', $expectedSetCode);
        } elseif (count($result['set_codes']) === 1) {
            $redirect = redirect()->route('admin.catalog.sets.show', $result['set_codes'][0]);
        }

        return $redirect->with('success', $message);
    }
}
