<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudentRequest;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Major;
use App\Models\StudentParent;
use App\Services\StudentService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    protected StudentService $service;

    public function __construct(StudentService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $students = $this->service->getAll($request->all());
        $classes = SchoolClass::where('is_active', true)->orderBy('grade_level')->orderBy('name')->get();
        $majors = Major::orderBy('name')->get();
        return view('admin.students.index', compact('students', 'classes', 'majors'));
    }

    public function create()
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('grade_level')->orderBy('name')->get();
        $parents = StudentParent::orderBy('name')->get();
        return view('admin.students.create', compact('classes', 'parents'));
    }

    public function store(StudentRequest $request)
    {
        $student = $this->service->store($request->validated());
        ActivityLogService::log('create', "Menambahkan data siswa: {$student->name}", $student, null, 'Data Siswa');
        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function show(Student $student)
    {
        return redirect()->route('admin.students.edit', $student);
    }

    public function edit(Student $student)
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('grade_level')->orderBy('name')->get();
        $parents = StudentParent::orderBy('name')->get();
        return view('admin.students.edit', compact('student', 'classes', 'parents'));
    }

    public function update(StudentRequest $request, Student $student)
    {
        $original = $student->getOriginal();
        $this->service->update($student, $request->validated());
        ActivityLogService::log('update', "Mengubah data siswa: {$student->name}", $student, ['old' => $original], 'Data Siswa');
        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student)
    {
        try {
            $name = $student->name;
            $this->service->delete($student);
            ActivityLogService::log('delete', "Menghapus data siswa: {$name}", null, null, 'Data Siswa');
            return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('admin.students.index')->with('error', $e->getMessage());
        }
    }
}
