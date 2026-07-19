<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Templates\StudentImportWithParentsExport;
use App\Exports\Templates\TeacherImportTemplate;
use App\Exports\Templates\ClassImportTemplate;
use App\Exports\Templates\ScheduleImportTemplate;

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
            $previewRows = $this->service->preview($file->getRealPath(), $type);
            
            // Store preview in session for committing later
            session([
                'import_type' => $type,
                'import_rows' => array_column($previewRows, 'data'),
            ]);

            return view('admin.imports.preview', compact('previewRows', 'type'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    public function commit()
    {
        $type = session('import_type');
        $rows = session('import_rows');

        if (!$type || !$rows) {
            return redirect()->route('admin.imports.index')->with('error', 'Tidak ada data import yang sedang ditangguhkan.');
        }

        try {
            $successCount = $this->service->import($rows, $type);
            
            // Clear session data
            session()->forget(['import_type', 'import_rows']);

            return redirect()->route('admin.imports.index')->with('success', "Berhasil mengimpor {$successCount} data {$type}.");
        } catch (\Exception $e) {
            return redirect()->route('admin.imports.index')->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        session()->forget(['import_type', 'import_rows']);
        return redirect()->route('admin.imports.index')->with('success', 'Import data dibatalkan.');
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
