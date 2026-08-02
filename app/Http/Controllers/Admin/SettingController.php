<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\GradeSessionSetting;
use App\Models\SchoolSession;
use App\Models\Setting;
use App\Services\ActivityLogService;
use App\Services\DateTimeService;
use App\Services\SchoolSessionResolverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function __construct(
        protected DateTimeService             $dateTimeService,
        protected SchoolSessionResolverService $sessionResolver
    ) {}

    // =========================================================================
    // MAIN PAGE
    // =========================================================================

    public function index()
    {
        $settings          = Setting::all()->keyBy('key');
        $sessions          = SchoolSession::orderBy('gate_open_time')->get();
        $activeAcademicYear = AcademicYear::active()->first();
        $gradeMappings     = $activeAcademicYear
            ? GradeSessionSetting::where('academic_year_id', $activeAcademicYear->id)
                ->with('schoolSession')->get()->keyBy('grade_level')
            : collect();
        $academicYears     = AcademicYear::orderBy('name', 'desc')->get();

        return view('admin.settings.index', compact(
            'settings',
            'sessions',
            'activeAcademicYear',
            'gradeMappings',
            'academicYears'
        ));
    }

    // =========================================================================
    // GENERAL SETTINGS (AJAX)
    // =========================================================================

    public function update(Request $request): JsonResponse
    {
        $data = $request->except('_token');

        $allowedKeys = Setting::pluck('key')->all();

        foreach ($data as $key => $value) {
            if (!in_array($key, $allowedKeys, true)) {
                continue;
            }

            $setting = Setting::where('key', $key)->first();
            if ($setting) {
                if ($setting->type === 'boolean' || $setting->type === 'bool') {
                    $val = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                } else {
                    $val = $value;
                }
                Setting::setVal($key, $val, $setting->group);
            }
        }

        ActivityLogService::log('update_settings', 'Memperbarui pengaturan sistem', null, $data);

        // Clear caches
        DateTimeService::clearCache();

        return response()->json(['success' => true, 'message' => 'Pengaturan berhasil disimpan.']);
    }

    // =========================================================================
    // SIMULATION SETTINGS (AJAX)
    // =========================================================================

    public function updateSimulation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'simulation_enabled'     => 'required|in:true,false,1,0',
            'simulation_date'        => 'nullable|date',
            'simulation_time'        => 'nullable|date_format:H:i',
            'simulation_day_override'=> ['nullable', Rule::in(['Automatic', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $keys = [
            'simulation_enabled',
            'simulation_date',
            'simulation_time',
            'simulation_day_override',
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                Setting::setVal($key, $request->input($key), 'simulation');
            }
        }

        DateTimeService::clearCache();

        ActivityLogService::log('update_simulation', 'Memperbarui pengaturan simulasi', null, $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan simulasi berhasil disimpan.',
            'current' => [
                'datetime' => $this->dateTimeService->now()->format('d-m-Y H:i:s'),
                'day'      => $this->dateTimeService->currentDay(),
                'enabled'  => $this->dateTimeService->isSimulationEnabled(),
            ],
        ]);
    }

    // =========================================================================
    // SESSION MANAGEMENT (AJAX)
    // =========================================================================

    public function storeSessions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sessions'                          => 'required|array|min:1',
            'sessions.*.id'                     => 'nullable|exists:school_sessions,id',
            'sessions.*.name'                   => 'required|string|max:50',
            'sessions.*.gate_open_time'         => 'required|date_format:H:i',
            'sessions.*.school_start_time'      => 'required|date_format:H:i',
            'sessions.*.late_threshold_minutes' => 'required|integer|min:0|max:120',
            'sessions.*.gate_close_time'        => 'required|date_format:H:i',
            'sessions.*.auto_alpha_time'        => 'required|date_format:H:i',
            'sessions.*.school_end_time'        => 'required|date_format:H:i',
            'sessions.*.is_active'              => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $errors = [];
        foreach ($request->sessions as $idx => $sessionData) {
            $err = $this->validateTimeSequence($sessionData);
            if ($err) {
                $errors["sessions.{$idx}"] = $err;
            }
        }

        if (!empty($errors)) {
            return response()->json(['success' => false, 'errors' => $errors], 422);
        }

        foreach ($request->sessions as $sessionData) {
            if (!empty($sessionData['id'])) {
                $session = SchoolSession::findOrFail($sessionData['id']);
                $session->update($sessionData);
            } else {
                SchoolSession::create($sessionData);
            }
        }

        SchoolSessionResolverService::clearCache();

        return response()->json(['success' => true, 'message' => 'Sesi sekolah berhasil disimpan.']);
    }

    public function destroySession(SchoolSession $session): JsonResponse
    {
        // Prevent deleting if classes are linked
        if ($session->classes()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak dapat dihapus karena masih digunakan oleh kelas.',
            ], 422);
        }

        $session->delete();

        SchoolSessionResolverService::clearCache();

        return response()->json(['success' => true, 'message' => 'Sesi berhasil dihapus.']);
    }

    // =========================================================================
    // GRADE SESSION MAPPING (AJAX)
    // =========================================================================

    public function updateGradeMappings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'academic_year_id'             => 'required|exists:academic_years,id',
            'mappings'                     => 'required|array',
            'mappings.*.grade_level'       => 'required|integer|in:10,11,12',
            'mappings.*.school_session_id' => 'nullable|exists:school_sessions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $academicYearId = $request->academic_year_id;

        foreach ($request->mappings as $mapping) {
            if (empty($mapping['school_session_id'])) {
                GradeSessionSetting::where('academic_year_id', $academicYearId)
                    ->where('grade_level', $mapping['grade_level'])
                    ->delete();
                continue;
            }

            GradeSessionSetting::updateOrCreate(
                [
                    'academic_year_id' => $academicYearId,
                    'grade_level'      => $mapping['grade_level'],
                ],
                ['school_session_id' => $mapping['school_session_id']]
            );
        }

        SchoolSessionResolverService::clearCache();
        ActivityLogService::log('update_grade_session_mapping', 'Memperbarui mapping sesi per tingkat', null, $request->all());

        return response()->json(['success' => true, 'message' => 'Mapping sesi per tingkat berhasil disimpan.']);
    }

    // =========================================================================
    // SESSION PREVIEW API (for status card)
    // =========================================================================

    public function sessionPreview(): JsonResponse
    {
        $now     = $this->dateTimeService->now();
        $sessions = SchoolSession::where('is_active', true)->orderBy('gate_open_time')->get();

        $preview = $sessions->map(function (SchoolSession $session) use ($now) {
            $gateOpen  = now()->setTimeFromTimeString($session->gate_open_time);
            $schoolEnd = now()->setTimeFromTimeString($session->school_end_time);
            $status    = 'Belum Mulai';

            if ($now->gte($gateOpen) && $now->lte($schoolEnd)) {
                $status = 'Sedang Berlangsung';
            } elseif ($now->gt($schoolEnd)) {
                $status = 'Selesai';
            }

            return [
                'id'                 => $session->id,
                'name'               => $session->name,
                'gate_open_time'     => substr($session->gate_open_time, 0, 5),
                'school_start_time'  => substr($session->school_start_time, 0, 5),
                'school_end_time'    => substr($session->school_end_time, 0, 5),
                'status'             => $status,
            ];
        });

        return response()->json([
            'success'     => true,
            'sessions'    => $preview,
            'current_time'=> $now->format('H:i'),
            'current_day' => $this->dateTimeService->currentDay(),
        ]);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    protected function validateTimeSequence(array $s): ?string
    {
        // gate_open < school_start < gate_close < auto_alpha < school_end
        $fields = [
            'gate_open_time'    => $s['gate_open_time'],
            'school_start_time' => $s['school_start_time'],
            'gate_close_time'   => $s['gate_close_time'],
            'auto_alpha_time'   => $s['auto_alpha_time'],
            'school_end_time'   => $s['school_end_time'],
        ];

        $times = array_map(fn ($t) => strtotime("1970-01-01 {$t}:00"), $fields);
        $keys  = array_keys($times);

        for ($i = 0; $i < count($keys) - 1; $i++) {
            if ($times[$keys[$i]] >= $times[$keys[$i + 1]]) {
                return "Urutan waktu tidak valid: {$keys[$i]} harus sebelum {$keys[$i + 1]}.";
            }
        }

        return null;
    }
}
