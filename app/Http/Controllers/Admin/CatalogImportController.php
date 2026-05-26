<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PlateCsvImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogImportController extends Controller
{
    public function create(): View
    {
        return view('admin.catalog.import');
    }

    public function store(Request $request, PlateCsvImporter $importer): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:20480'],
        ], [], [
            'csv_file' => 'CSV file',
        ]);

        $result = $importer->importFromUploadedFile($request->file('csv_file'));

        $message = "Import finished. Added {$result['imported']} plates";
        $message .= $result['skipped'] > 0 ? ", skipped {$result['skipped']} rows." : '.';

        if (! empty($result['errors'])) {
            session()->flash('error', implode(' ', array_slice($result['errors'], 0, 3)));
        }

        return redirect()
            ->route('admin.catalog.import.create')
            ->with('success', $message);
    }
}
