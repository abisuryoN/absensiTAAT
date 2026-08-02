<?php

namespace App\Console\Commands;

use App\Services\DateTimeService;
use App\Services\HolidayService;
use Illuminate\Console\Command;

/**
 * HolidayCheck
 *
 * Warms up the holiday cache for today + the next N days.
 * Run this once daily (early morning) so scans never hit the external API.
 */
class HolidayCheck extends Command
{
    protected $signature   = 'attendance:holiday-check {--days=7 : How many days ahead to pre-fetch}';
    protected $description = 'Pre-fetch and cache national holiday data for upcoming days';

    public function __construct(
        protected HolidayService  $holidayService,
        protected DateTimeService $dateTimeService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $days  = (int) $this->option('days');
        $today = $this->dateTimeService->today();

        $this->info("[HolidayCheck] Pre-fetching holiday data for next {$days} days...");

        $holidays = [];

        for ($i = 0; $i <= $days; $i++) {
            $date   = $today->copy()->addDays($i);
            $result = $this->holidayService->isHoliday($date);

            if ($result['is_holiday']) {
                $holidays[] = "{$date->format('d M Y')} — {$result['reason']}";
            }
        }

        if (empty($holidays)) {
            $this->info("[HolidayCheck] No holidays found in the next {$days} days.");
        } else {
            $this->info("[HolidayCheck] Found holidays:");
            foreach ($holidays as $h) {
                $this->line("  • {$h}");
            }
        }

        $this->info("[HolidayCheck] Holiday cache warmed successfully.");

        return self::SUCCESS;
    }
}
