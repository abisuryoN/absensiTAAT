<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MajorRequest;
use App\Models\Major;
use App\Services\MajorService;
use Illuminate\Http\Request;

class MajorController extends Controller
{
    protected MajorService $service;

    public function __construct(MajorService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $majors = $this->service->getAll($request->all());
        return view('admin.majors.index', compact('majors'));
    }

    public function create()
    {
        return view('admin.majors.create');
    }

    public function store(MajorRequest $request)
    {
        $this->service->store($request->validated());
        return redirect()->route('admin.majors.index')->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function edit(Major $major)
    {
        return view('admin.majors.edit', compact('major'));
    }

    public function update(MajorRequest $request, Major $major)
    {
        $this->service->update($major, $request->validated());
        return redirect()->route('admin.majors.index')->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy(Major $major)
    {
        try {
            $this->service->delete($major);
            return redirect()->route('admin.majors.index')->with('success', 'Jurusan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('admin.majors.index')->with('error', $e->getMessage());
        }
    }
}
