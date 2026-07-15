<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ParentRequest;
use App\Models\StudentParent;
use App\Services\ParentService;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    protected ParentService $service;

    public function __construct(ParentService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $parents = $this->service->getAll($request->all());
        return view('admin.parents.index', compact('parents'));
    }

    public function create()
    {
        return view('admin.parents.create');
    }

    public function store(ParentRequest $request)
    {
        $this->service->store($request->validated());
        return redirect()->route('admin.parents.index')->with('success', 'Data orang tua berhasil ditambahkan.');
    }

    public function edit(StudentParent $parent)
    {
        return view('admin.parents.edit', compact('parent'));
    }

    public function update(ParentRequest $request, StudentParent $parent)
    {
        $this->service->update($parent, $request->validated());
        return redirect()->route('admin.parents.index')->with('success', 'Data orang tua berhasil diperbarui.');
    }

    public function destroy(StudentParent $parent)
    {
        try {
            $this->service->delete($parent);
            return redirect()->route('admin.parents.index')->with('success', 'Data orang tua berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('admin.parents.index')->with('error', $e->getMessage());
        }
    }
}
