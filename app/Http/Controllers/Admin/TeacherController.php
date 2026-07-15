<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeacherRequest;
use App\Models\Teacher;
use App\Models\Subject;
use App\Services\TeacherService;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    protected TeacherService $service;

    public function __construct(TeacherService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $teachers = $this->service->getAll($request->all());
        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        return view('admin.teachers.create', compact('subjects'));
    }

    public function store(TeacherRequest $request)
    {
        $this->service->store($request->validated());
        return redirect()->route('admin.teachers.index')->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function edit(Teacher $teacher)
    {
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        return view('admin.teachers.edit', compact('teacher', 'subjects'));
    }

    public function update(TeacherRequest $request, Teacher $teacher)
    {
        $this->service->update($teacher, $request->validated());
        return redirect()->route('admin.teachers.index')->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Teacher $teacher)
    {
        try {
            $this->service->delete($teacher);
            return redirect()->route('admin.teachers.index')->with('success', 'Data guru berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('admin.teachers.index')->with('error', $e->getMessage());
        }
    }
}
