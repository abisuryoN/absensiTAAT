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

    public function create()
    {
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $majors = Major::where('is_active', true)->orderBy('name')->get();
        $teachers = Teacher::where('is_active', true)->orderBy('name')->get();
        return view('admin.classes.create', compact('academicYears', 'majors', 'teachers'));
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
        return view('admin.classes.edit', compact('class', 'academicYears', 'majors', 'teachers'));
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
