<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\AttendanceGate;
use App\Models\SchoolSession;
use App\Models\Semester;
use App\Models\Setting;
use App\Services\ActivityLogService;
use App\Services\DateTimeService;
use App\Services\SchoolSessionResolverService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * AutoCheckOut
 *
 * Marks students who scanned in (hadir/terlambat) but never scanned out.
 * In multi-session mode, triggers per session when school_end_time passes.
 * In single-session mode, triggers at the global checkout_time setting.
 */
class AutoCheckOut extends Command
{
    protected $signature   = 'attendance:auto-checkout {--date= : Target date in Y-m-d (defaults to simulated today)}';
    protected $description = 'Auto-set checkout time for students who never scanned out';

    public function __construct(
        protected DateTimeService              $dateTimeService,
        protected SchoolSessionResolverService $sessionResolver,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $date      = $this->option('date') ?? $this->dateTimeService->currentDate();
        $now       = $this->dateTimeService->now();
        $multiMode = $this->dateTimeService->isMultipleSessionsEnabled();

        $this->info("[AutoCheckOut] Running for date: {$date}, multi-session: " . ($multiMode ? 'yes' : 'no'));

        if ($this->dateTimeService->isWeekend()) {
            $this->warn("[AutoCheckOut] Weekend. Skipping.");
            return self::SUCCESS;
        }

        $academicYear = AcademicYear::active()->first();
        $semester     = Semester::active()->first();
        if (!$academicYear || !$semester) {
            $this->warn("[AutoCheckOut] No active academic year or semester.");
            return self::SUCCESS;
        }

        $count = DB::transaction(function () use ($date, $now, $multiMode) {
            $records = AttendanceGate::where('date', $date)
                ->whereNull('time_out')
                ->whereIn('status', ['hadir', 'terlambat'])
                ->with('student.schoolClass.schoolSession')
                ->get();

            $updated = 0;

            foreach ($records as $record) {
                if ($multiMode) {
                    $session = $this->sessionResolver->resolve($record->student);
                    if (!$session) continue;
                    $schoolEnd = Carbon::createFromFormat('H:i', substr($session->school_end_time, 0, 5));
                    if ($now->lt($schoolEnd)) continue; // Not yet end of session
                    $checkoutTime = $session->school_end_time;
                } else {
                    $checkoutStr  = Setting::getVal('auto_checkout_time', '17:00');
                    $checkoutTime = Carbon::createFromFormat('H:i', substr($checkoutStr, 0, 5));
                    if ($now->lt($checkoutTime)) continue;
                    $checkoutTime = $checkoutStr . ':00';
                }

                $record->update(['time_out' => $checkoutTime]);
                $updated++;
            }

            return $updated;
        });

        $this->info("[AutoCheckOut] Updated {$count} records.");

        if ($count > 0) {
            ActivityLogService::log('auto_checkout', "Auto-checkout: {$count} siswa dikonfirmasi pulang pada {$date}", null);
        }

        return self::SUCCESS;
    }
}
