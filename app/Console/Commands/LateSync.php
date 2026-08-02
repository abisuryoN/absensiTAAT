<?php

namespace App\Console\Commands;

use App\Models\AttendanceGate;
use App\Services\ActivityLogService;
use App\Services\DateTimeService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * LateSync
 *
 * Re-evaluates the status of attendance records created within the
 * "gate open" window that may have initially been marked 'hadir' but
 * should be 'terlambat' once the late threshold passes, or vice-versa
 * if simulation mode changed the thresholds mid-day.
 *
 * Also useful for reconciling records when sessions were edited after
 * students already scanned in.
 */
class LateSync extends Command
{
    protected $signature   = 'attendance:late-sync {--date= : Target date (Y-m-d, defaults to simulated today)}';
    protected $description = 'Re-evaluate and fix hadir/terlambat statuses based on current session configuration';

    public function __construct(
        protected DateTimeService $dateTimeService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $date       = $this->option('date') ?? $this->dateTimeService->currentDate();
        $gateService = app(\App\Services\AttendanceGateService::class);
        $resolver    = app(\App\Services\SchoolSessionResolverService::class);
        $multiMode   = $this->dateTimeService->isMultipleSessionsEnabled();

        $this->info("[LateSync] Re-evaluating attendance statuses for {$date}...");

        $records = AttendanceGate::where('date', $date)
            ->whereIn('status', ['hadir', 'terlambat'])
            ->with('student.schoolClass.schoolSession')
            ->get();

        $changed = 0;

        DB::transaction(function () use ($records, $gateService, $resolver, $multiMode, &$changed) {
            foreach ($records as $record) {
                $session = $multiMode ? $resolver->resolve($record->student) : null;
                $newStatus = $gateService->getStatusByTime($record->time_in, $session);

                if ($newStatus !== $record->status) {
                    $old = $record->status;
                    $record->update(['status' => $newStatus]);
                    $changed++;
                    $this->line("  ↺ {$record->student->name}: {$old} → {$newStatus}");
                }
            }
        });

        $this->info("[LateSync] Done. {$changed} records updated.");

        if ($changed > 0) {
            ActivityLogService::log(
                'late_sync',
                "LateSync: {$changed} status absensi diperbarui pada {$date}",
                null
            );
        }

        return self::SUCCESS;
    }
}
