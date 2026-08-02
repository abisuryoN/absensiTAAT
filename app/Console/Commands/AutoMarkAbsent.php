<?php

namespace App\Console\Commands;

use App\Services\AttendanceGateService;
use App\Services\DateTimeService;
use Illuminate\Console\Command;

class AutoMarkAbsent extends Command
{
    protected $signature   = 'attendance:auto-absent {--date= : Target date in Y-m-d format (defaults to simulated today)}';
    protected $description = 'Mark students without attendance today as tidak_hadir (auto-alpha)';

    public function __construct(
        protected AttendanceGateService $gateService,
        protected DateTimeService        $dateTimeService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $date = $this->option('date') ?? $this->dateTimeService->currentDate();

        $this->info("[AutoMarkAbsent] Running for date: {$date}");

        // Skip weekends
        if ($this->dateTimeService->isWeekend()) {
            $this->warn("[AutoMarkAbsent] Weekend detected ({$this->dateTimeService->currentDay()}). Skipping.");
            return self::SUCCESS;
        }

        try {
            $count = $this->gateService->markAbsentStudents($date);
            $this->info("[AutoMarkAbsent] Marked {$count} students as tidak_hadir.");
        } catch (\Throwable $e) {
            $this->error("[AutoMarkAbsent] Error: {$e->getMessage()}");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
