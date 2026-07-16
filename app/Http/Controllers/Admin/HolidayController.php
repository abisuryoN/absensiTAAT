<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HolidayRequest;
use App\Models\Holiday;
use App\Models\AcademicYear;
use App\Services\HolidayService;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    protected HolidayService $service;

    public function __construct(HolidayService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $holidays = $this->service->getAll($request->all());
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        return view('admin.holidays.index', compact('holidays', 'academicYears'));
    }

    public function create()
    {
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        return view('admin.holidays.create', compact('academicYears'));
    }

    public function store(HolidayRequest $request)
    {
        $this->service->store($request->validated());
        return redirect()->route('admin.holidays.index')->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function edit(Holiday $holiday)
    {
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        return view('admin.holidays.edit', compact('holiday', 'academicYears'));
    }

    public function update(HolidayRequest $request, Holiday $holiday)
    {
        $this->service->update($holiday, $request->validated());
        return redirect()->route('admin.holidays.index')->with('success', 'Hari libur berhasil diperbarui.');
    }

    public function destroy(Holiday $holiday)
    {
        try {
            $this->service->delete($holiday);
            return redirect()->route('admin.holidays.index')->with('success', 'Hari libur berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('admin.holidays.index')->with('error', $e->getMessage());
        }
    }

    public function sync(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        try {
            $result = $this->service->syncFromApi($request->academic_year_id);
            
            if ($result['success']) {
                return redirect()->route('admin.holidays.index')->with('success', $result['message']);
            } else {
                return redirect()->route('admin.holidays.index')->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.holidays.index')->with('error', 'Gagal sinkronisasi: ' . $e->getMessage());
        }
    }
}
