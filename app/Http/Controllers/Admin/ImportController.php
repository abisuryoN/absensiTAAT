<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImportService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Templates\StudentImportWithParentsExport;
use App\Exports\Templates\TeacherImportTemplate;
use App\Exports\Templates\ClassImportTemplate;
use App\Exports\Templates\ScheduleImportTemplate;
use App\Exports\ImportErrorReportExport;

class ImportController extends Controller
{
    protected ImportService $service;

    public function __construct(ImportService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('admin.imports.index');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'type' => 'required|in:students,teachers,classes,schedules',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120', // Max 5MB
        ], [
            'file.mimes' => 'Format file harus berupa Excel (.xlsx, .xls) atau CSV.',
            'file.max' => 'Ukuran file maksimal adalah 5MB.',
        ]);

        $type = $request->input('type');
        $file = $request->file('file');

        try {
            // Validate template and parse all rows
            $result = $this->service->previewAndValidate($file->getRealPath(), $type);

            // If there are validation errors, save them to session and redirect to preview page
            if (!empty($result['errors'])) {
                session([
                    'import_errors' => $result['errors'],
                    'import_type'   => $type,
                    'import_stats'  => [
                        'total_rows'         => $result['total_rows'],
                        'valid_rows_count'   => $result['valid_rows_count'],
                        'invalid_rows_count' => $result['invalid_rows_count'],
                    ]
                ]);

                $errors = $result['errors'];
                $stats = session('import_stats');

                return view('admin.imports.preview', compact('errors', 'type', 'stats'));
            }

            // If ALL rows are valid: Save immediately! (Atomic all-or-nothing)
            $successCount = $this->service->import($result['valid_data'], $type);

            $typeLabel = match($type) {
                'students' => 'Siswa',
                'teachers' => 'Guru',
                'classes'  => 'Kelas',
                'schedules'=> 'Jadwal',
                default    => ucfirst($type),
            };
            ActivityLogService::logImport($typeLabel, $successCount);

            return redirect()->route('admin.imports.index')
                ->with('success', "Berhasil mengimpor {$successCount} data {$typeLabel}.");

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Download error report for currently failed import.
     */
    public function downloadErrorReport()
    {
        $errors = session('import_errors');
        $type = session('import_type');

        if (empty($errors)) {
            return redirect()->route('admin.imports.index')->with('error', 'Tidak ada laporan error untuk diunduh.');
        }

        $typeLabel = match($type) {
            'students' => 'Siswa',
            'teachers' => 'Guru',
            'classes'  => 'Kelas',
            'schedules'=> 'Jadwal',
            default    => ucfirst($type),
        };

        $filename = "Laporan_Error_Import_" . $typeLabel . "_" . date('Ymd_His') . ".xlsx";

        return Excel::download(new ImportErrorReportExport($errors), $filename);
    }

    public function commit()
    {
        // Deprecated as valid files are processed automatically and invalid files are aborted
        return redirect()->route('admin.imports.index')->with('error', 'Metode ini tidak lagi digunakan.');
    }

    public function cancel()
    {
        session()->forget(['import_errors', 'import_type', 'import_stats']);
        return redirect()->route('admin.imports.index')->with('success', 'Proses import dibatalkan.');
    }

    public function downloadTemplate($type)
    {
        $templates = [
            'students' => [
                'class' => StudentImportWithParentsExport::class,
                'filename' => 'Template_Import_Siswa.xlsx'
            ],
            'teachers' => [
                'class' => TeacherImportTemplate::class,
                'filename' => 'Template_Import_Guru.xlsx'
            ],
            'classes' => [
                'class' => ClassImportTemplate::class,
                'filename' => 'Template_Import_Kelas.xlsx'
            ],
            'schedules' => [
                'class' => ScheduleImportTemplate::class,
                'filename' => 'Template_Import_Jadwal.xlsx'
            ],
        ];

        if (!isset($templates[$type])) {
            return back()->with('error', 'Tipe template tidak valid.');
        }

        $template = $templates[$type];

        return Excel::download(new $template['class'], $template['filename']);
    }
}
