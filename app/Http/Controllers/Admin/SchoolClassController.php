<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SchoolClassRequest;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\Teacher;
use App\Services\SchoolClassService;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    protected SchoolClassService $service;

    public function __construct(SchoolClassService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $classes = $this->service->getAll($request->all());
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $majors = Major::where('is_active', true)->orderBy('name')->get();
        return view('admin.classes.index', compact('classes', 'academicYears', 'majors'));
    }

    public function show(SchoolClass $class, Request $request)
    {
        // Eager load relasi yang diperlukan
        $class->load(['academicYear', 'major', 'homeroomTeacher', 'schoolSession']);

        // Query siswa dengan pencarian dan pagination
        $studentsQuery = $class->students()->with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $studentsQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $students = $studentsQuery->orderBy('name')->paginate(20)->withQueryString();
        $totalStudents = $class->students()->count();

        return view('admin.classes.show', compact('class', 'students', 'totalStudents'));
    }

    public function create()
    {
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $majors = Major::where('is_active', true)->orderBy('name')->get();
        $teachers = Teacher::where('is_active', true)->orderBy('name')->get();
        $schoolSessions = \App\Models\SchoolSession::where('is_active', true)->orderBy('gate_open_time')->get();
        return view('admin.classes.create', compact('academicYears', 'majors', 'teachers', 'schoolSessions'));
    }

    public function store(SchoolClassRequest $request)
    {
        $this->service->store($request->validated());
        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(SchoolClass $class)
    {
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $majors = Major::where('is_active', true)->orderBy('name')->get();
        $teachers = Teacher::where('is_active', true)->orderBy('name')->get();
        $schoolSessions = \App\Models\SchoolSession::where('is_active', true)->orderBy('gate_open_time')->get();
        return view('admin.classes.edit', compact('class', 'academicYears', 'majors', 'teachers', 'schoolSessions'));
    }

    public function update(SchoolClassRequest $request, SchoolClass $class)
    {
        $this->service->update($class, $request->validated());
        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(SchoolClass $class)
    {
        try {
            $this->service->delete($class);
            return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('admin.classes.index')->with('error', $e->getMessage());
        }
    }
}
