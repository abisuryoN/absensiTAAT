<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * DateTimeService
 *
 * Single source of truth for all date/time operations across the application.
 * Supports a simulation mode where date, time, and day can be overridden
 * without touching the server clock.
 *
 * Usage:
 *   app(DateTimeService::class)->now()
 *   app(DateTimeService::class)->currentDay()
 */
class DateTimeService
{
    // Cache TTL for settings reads
    protected const CACHE_TTL = 300; // 5 minutes

    // ── Core time methods ─────────────────────────────────────────────────────

    /**
     * Returns a Carbon instance representing the current date+time.
     * If simulation is enabled, merges the simulated date with simulated time.
     */
    public function now(): Carbon
    {
        if (!$this->isSimulationEnabled()) {
            return Carbon::now();
        }

        $date = $this->getSimulationDate();
        $time = $this->getSimulationTime();

        if ($date && $time) {
            return Carbon::createFromFormat('Y-m-d H:i', "{$date} {$time}");
        }
        if ($date) {
            return Carbon::parse($date)->setTime(
                Carbon::now()->hour,
                Carbon::now()->minute,
                Carbon::now()->second
            );
        }

        return Carbon::now();
    }

    /**
     * Returns a Carbon instance for the start of the current (simulated) day.
     */
    public function today(): Carbon
    {
        return $this->now()->startOfDay();
    }

    /**
     * Returns the current date string formatted as Y-m-d.
     */
    public function currentDate(): string
    {
        return $this->now()->format('Y-m-d');
    }

    /**
     * Returns the current date in a readable Indonesian format (d-m-Y).
     */
    public function todayDate(): string
    {
        return $this->now()->format('d-m-Y');
    }

    /**
     * Returns the current time as H:i:s string.
     */
    public function currentTime(): string
    {
        return $this->now()->format('H:i:s');
    }

    /**
     * Returns the current day name in Indonesian (Senin, Selasa, etc.).
     * Respects the simulation day override.
     */
    public function currentDay(): string
    {
        $override = $this->getSimulationDayOverride();

        if ($this->isSimulationEnabled() && $override !== 'Automatic' && $override !== null) {
            return $override;
        }

        // Map Carbon English day name -> Indonesian
        $dayMap = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu',
        ];

        return $dayMap[$this->now()->format('l')] ?? $this->now()->format('l');
    }

    /**
     * Returns true if today (simulated or real) is Saturday or Sunday.
     */
    public function isWeekend(): bool
    {
        $day = $this->currentDay();
        return in_array($day, ['Sabtu', 'Minggu'], true);
    }

    /**
     * Delegates to HolidayService to check if simulated/real today is a holiday.
     */
    public function isSchoolHoliday(): array
    {
        $holidayService = app(HolidayService::class);
        return $holidayService->isHoliday($this->today());
    }

    // ── Feature flags ─────────────────────────────────────────────────────────

    public function isSimulationEnabled(): bool
    {
        return Cache::remember('setting.simulation_enabled', self::CACHE_TTL, function () {
            return filter_var(Setting::getVal('simulation_enabled', false), FILTER_VALIDATE_BOOLEAN);
        });
    }

    public function isMultipleSessionsEnabled(): bool
    {
        return Cache::remember('setting.multiple_sessions_enabled', self::CACHE_TTL, function () {
            return filter_var(Setting::getVal('multiple_sessions_enabled', false), FILTER_VALIDATE_BOOLEAN);
        });
    }

    // ── Current session (for logged-in student) ───────────────────────────────

    /**
     * Resolves the current school session for the authenticated student.
     * Falls back to the default session if no student is logged in.
     */
    public function currentSchoolSession(): ?\App\Models\SchoolSession
    {
        if (!$this->isMultipleSessionsEnabled()) {
            return null;
        }

        $user = auth()->user();
        if ($user && $user->hasRole('siswa') && $user->student) {
            return app(SchoolSessionResolverService::class)->resolve($user->student);
        }

        return app(SchoolSessionResolverService::class)->getDefaultSession();
    }

    // ── Simulation getters ────────────────────────────────────────────────────

    protected function getSimulationDate(): ?string
    {
        return Cache::remember('setting.simulation_date', self::CACHE_TTL, fn () =>
            Setting::getVal('simulation_date')
        );
    }

    protected function getSimulationTime(): ?string
    {
        return Cache::remember('setting.simulation_time', self::CACHE_TTL, fn () =>
            Setting::getVal('simulation_time')
        );
    }

    protected function getSimulationDayOverride(): ?string
    {
        return Cache::remember('setting.simulation_day_override', self::CACHE_TTL, fn () =>
            Setting::getVal('simulation_day_override', 'Automatic')
        );
    }

    // ── Cache management ──────────────────────────────────────────────────────

    /**
     * Clear all DateTimeService-related caches.
     * Call this after saving simulation settings.
     */
    public static function clearCache(): void
    {
        Cache::forget('setting.simulation_enabled');
        Cache::forget('setting.simulation_date');
        Cache::forget('setting.simulation_time');
        Cache::forget('setting.simulation_day_override');
        Cache::forget('setting.multiple_sessions_enabled');
    }
}
