<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AcademicYearRequest;
use App\Models\AcademicYear;
use App\Services\AcademicYearService;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    protected AcademicYearService $service;

    public function __construct(AcademicYearService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $academicYears = $this->service->getAll($request->all());
        return view('admin.academic-years.index', compact('academicYears'));
    }

    public function create()
    {
        return view('admin.academic-years.create');
    }

    public function store(AcademicYearRequest $request)
    {
        $this->service->store($request->validated());
        return redirect()->route('admin.academic-years.index')->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function edit(AcademicYear $academicYear)
    {
        return view('admin.academic-years.edit', compact('academicYear'));
    }

    public function update(AcademicYearRequest $request, AcademicYear $academicYear)
    {
        $this->service->update($academicYear, $request->validated());
        return redirect()->route('admin.academic-years.index')->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        try {
            $this->service->delete($academicYear);
            return redirect()->route('admin.academic-years.index')->with('success', 'Tahun ajaran berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('admin.academic-years.index')->with('error', $e->getMessage());
        }
    }
}
