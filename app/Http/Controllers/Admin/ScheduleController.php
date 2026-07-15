<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScheduleRequest;
use App\Models\Schedule;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Services\ScheduleService;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    protected ScheduleService $service;

    public function __construct(ScheduleService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $schedules = $this->service->getAll($request->all());
        $classes = SchoolClass::where('is_active', true)->orderBy('grade_level')->orderBy('name')->get();
        $teachers = Teacher::where('is_active', true)->orderBy('name')->get();
        return view('admin.schedules.index', compact('schedules', 'classes', 'teachers'));
    }

    public function create()
    {
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::orderByDesc('start_date')->get();
        $teachers = Teacher::where('is_active', true)->orderBy('name')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        $classes = SchoolClass::where('is_active', true)->orderBy('grade_level')->orderBy('name')->get();
        return view('admin.schedules.create', compact('academicYears', 'semesters', 'teachers', 'subjects', 'classes'));
    }

    public function store(ScheduleRequest $request)
    {
        try {
            $this->service->store($request->validated());
            return redirect()->route('admin.schedules.index')->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit(Schedule $schedule)
    {
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::orderByDesc('start_date')->get();
        $teachers = Teacher::where('is_active', true)->orderBy('name')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        $classes = SchoolClass::where('is_active', true)->orderBy('grade_level')->orderBy('name')->get();
        return view('admin.schedules.edit', compact('schedule', 'academicYears', 'semesters', 'teachers', 'subjects', 'classes'));
    }

    public function update(ScheduleRequest $request, Schedule $schedule)
    {
        try {
            $this->service->update($schedule, $request->validated());
            return redirect()->route('admin.schedules.index')->with('success', 'Jadwal pelajaran berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(Schedule $schedule)
    {
        try {
            $this->service->delete($schedule);
            return redirect()->route('admin.schedules.index')->with('success', 'Jadwal pelajaran berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('admin.schedules.index')->with('error', $e->getMessage());
        }
    }
}
