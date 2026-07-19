<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ParentRequest;
use App\Models\StudentParent;
use App\Services\ParentService;
use App\Exports\ParentReferenceExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
        return redirect()->route('admin.parents.index')
            ->with('success', 'Data orang tua/wali berhasil ditambahkan.');
    }

    public function show(StudentParent $parent)
    {
        $parent->load(['user', 'students.class']);
        return view('admin.parents.show', compact('parent'));
    }

    public function edit(StudentParent $parent)
    {
        $parent->load('user');
        return view('admin.parents.edit', compact('parent'));
    }

    public function update(ParentRequest $request, StudentParent $parent)
    {
        $this->service->update($parent, $request->validated());
        return redirect()->route('admin.parents.index')
            ->with('success', 'Data orang tua/wali berhasil diperbarui.');
    }

    public function destroy(StudentParent $parent)
    {
        $this->service->delete($parent);
        return redirect()->route('admin.parents.index')
            ->with('success', 'Data orang tua/wali berhasil dihapus.');
    }

    /**
     * AJAX endpoint for the parent picker modal in student create/edit forms.
     */
    public function pickerSearch(Request $request)
    {
        $search  = $request->get('search', '');
        $parents = $this->service->pickerSearch($search, 10);

        // Return paginated JSON for the modal
        return response()->json([
            'data'          => $parents->items(),
            'current_page'  => $parents->currentPage(),
            'last_page'     => $parents->lastPage(),
            'total'         => $parents->total(),
        ]);
    }

    /**
     * Export all parents as Excel reference file for student import.
     */
    public function exportReference()
    {
        return Excel::download(
            new ParentReferenceExport(),
            'Referensi_Data_OrangTua_' . now()->format('Ymd') . '.xlsx'
        );
    }
}