<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SemesterRequest;
use App\Models\Semester;
use App\Models\AcademicYear;
use App\Services\SemesterService;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    protected SemesterService $service;

    public function __construct(SemesterService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $semesters = $this->service->getAll($request->all());
        return view('admin.semesters.index', compact('semesters'));
    }

    public function create()
    {
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        return view('admin.semesters.create', compact('academicYears'));
    }

    public function store(SemesterRequest $request)
    {
        $this->service->store($request->validated());
        return redirect()->route('admin.semesters.index')->with('success', 'Semester berhasil ditambahkan.');
    }

    public function edit(Semester $semester)
    {
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        return view('admin.semesters.edit', compact('semester', 'academicYears'));
    }

    public function update(SemesterRequest $request, Semester $semester)
    {
        $this->service->update($semester, $request->validated());
        return redirect()->route('admin.semesters.index')->with('success', 'Semester berhasil diperbarui.');
    }

    public function destroy(Semester $semester)
    {
        try {
            $this->service->delete($semester);
            return redirect()->route('admin.semesters.index')->with('success', 'Semester berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('admin.semesters.index')->with('error', $e->getMessage());
        }
    }
}
