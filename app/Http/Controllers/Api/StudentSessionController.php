<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DateTimeService;
use App\Services\SchoolSessionResolverService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentSessionController extends Controller
{
    public function __construct(
        protected DateTimeService              $dateTimeService,
        protected SchoolSessionResolverService $sessionResolver,
    ) {}

    /**
     * GET /api/student/session
     *
     * Returns the resolved school session for the authenticated student
     * along with current simulated time and gate window status.
     *
     * Used by the mobile app to know:
     *  - Which session the student belongs to
     *  - Whether the gate is currently open
     *  - Whether they're already past late threshold
     */
    public function show(Request $request): JsonResponse
    {
        $user    = $request->user();
        $student = $user->student ?? null;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Profil siswa tidak ditemukan.',
            ], 404);
        }

        $now     = $this->dateTimeService->now();
        $today   = $this->dateTimeService->currentDate();
        $day     = $this->dateTimeService->currentDay();
        $session = $this->sessionResolver->resolve($student);

        if (!$session) {
            return response()->json([
                'success'      => true,
                'session'      => null,
                'current_time' => $now->format('H:i:s'),
                'current_date' => $today,
                'current_day'  => $day,
                'gate_status'  => 'no_session',
                'message'      => 'Tidak ada sesi yang aktif.',
            ]);
        }

        // Compute gate status
        $gateOpen  = Carbon::createFromFormat('H:i', substr($session->gate_open_time, 0, 5));
        $gateClose = Carbon::createFromFormat('H:i', substr($session->gate_close_time, 0, 5));
        $schoolEnd = Carbon::createFromFormat('H:i', substr($session->school_end_time, 0, 5));
        $lateLimit = Carbon::createFromFormat('H:i', substr($session->school_start_time, 0, 5))
                           ->addMinutes($session->late_threshold_minutes);

        $gateStatus = match (true) {
            $now->lt($gateOpen)   => 'before_open',
            $now->between($gateOpen, $gateClose) => 'open',
            $now->between($gateClose, $schoolEnd) => 'closed_in_session',
            $now->gt($schoolEnd)  => 'session_ended',
            default               => 'unknown',
        };

        $isLate = $now->gt($lateLimit) && $gateStatus === 'open';

        return response()->json([
            'success'           => true,
            'session'           => [
                'id'                     => $session->id,
                'name'                   => $session->name,
                'gate_open_time'         => substr($session->gate_open_time, 0, 5),
                'school_start_time'      => substr($session->school_start_time, 0, 5),
                'late_threshold_minutes' => $session->late_threshold_minutes,
                'gate_close_time'        => substr($session->gate_close_time, 0, 5),
                'auto_alpha_time'        => substr($session->auto_alpha_time, 0, 5),
                'school_end_time'        => substr($session->school_end_time, 0, 5),
            ],
            'current_time'      => $now->format('H:i:s'),
            'current_date'      => $today,
            'current_day'       => $day,
            'gate_status'       => $gateStatus,
            'is_late'           => $isLate,
            'simulation_active' => $this->dateTimeService->isSimulationEnabled(),
        ]);
    }
}
