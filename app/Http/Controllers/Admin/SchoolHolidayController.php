<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolHoliday;
use App\Services\HolidayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SchoolHolidayController extends Controller
{
    public function __construct(protected HolidayService $holidayService)
    {
    }

    /**
     * Display listing of school holidays.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'year', 'month', 'is_active']);
        $holidays = $this->holidayService->getAllSchoolHolidays($filters, 15);

        // For year/month filter dropdowns
        $years = range(date('Y') - 2, date('Y') + 2);

        return view('admin.school-holidays.index', compact('holidays', 'filters', 'years'));
    }

    /**
     * Show form to create a new school holiday.
     */
    public function create()
    {
        return view('admin.school-holidays.create');
    }

    /**
     * Store a new school holiday.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:150'],
            'holiday_date' => [
                'required',
                'date',
                // Tidak boleh ada dua data aktif dengan tanggal yang sama
                function ($attribute, $value, $fail) {
                    $exists = SchoolHoliday::where('holiday_date', $value)
                        ->where('is_active', true)
                        ->exists();
                    if ($exists) {
                        $fail('Hari libur pada tanggal tersebut sudah ada.');
                    }
                },
            ],
            'description'  => ['nullable', 'string', 'max:500'],
            'is_active'    => ['boolean'],
        ], [
            'title.required'        => 'Judul hari libur wajib diisi.',
            'holiday_date.required' => 'Tanggal hari libur wajib diisi.',
            'holiday_date.date'     => 'Format tanggal tidak valid.',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_active']  = $request->boolean('is_active', true);

        $this->holidayService->storeSchoolHoliday($validated);

        return redirect()
            ->route('admin.school-holidays.index')
            ->with('success', 'Hari libur sekolah berhasil ditambahkan.');
    }

    /**
     * Show form to edit a school holiday.
     */
    public function edit(SchoolHoliday $schoolHoliday)
    {
        return view('admin.school-holidays.edit', compact('schoolHoliday'));
    }

    /**
     * Update an existing school holiday.
     */
    public function update(Request $request, SchoolHoliday $schoolHoliday)
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:150'],
            'holiday_date' => [
                'required',
                'date',
                // Tidak boleh ada dua data AKTIF dengan tanggal yang sama (kecuali record ini sendiri)
                function ($attribute, $value, $fail) use ($schoolHoliday, $request) {
                    $isActive = $request->boolean('is_active', true);
                    if ($isActive) {
                        $exists = SchoolHoliday::where('holiday_date', $value)
                            ->where('is_active', true)
                            ->where('id', '!=', $schoolHoliday->id)
                            ->exists();
                        if ($exists) {
                            $fail('Hari libur pada tanggal tersebut sudah ada.');
                        }
                    }
                },
            ],
            'description'  => ['nullable', 'string', 'max:500'],
            'is_active'    => ['boolean'],
        ], [
            'title.required'        => 'Judul hari libur wajib diisi.',
            'holiday_date.required' => 'Tanggal hari libur wajib diisi.',
            'holiday_date.date'     => 'Format tanggal tidak valid.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $this->holidayService->updateSchoolHoliday($schoolHoliday, $validated);

        return redirect()
            ->route('admin.school-holidays.index')
            ->with('success', 'Hari libur sekolah berhasil diperbarui.');
    }

    /**
     * Delete a school holiday.
     */
    public function destroy(SchoolHoliday $schoolHoliday)
    {
        $this->holidayService->deleteSchoolHoliday($schoolHoliday);

        return redirect()
            ->route('admin.school-holidays.index')
            ->with('success', 'Hari libur sekolah berhasil dihapus.');
    }
}
