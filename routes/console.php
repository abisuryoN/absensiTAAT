<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Cleanup expired QR tokens every 5 minutes ─────────────────────────────────
Schedule::command('qr:cleanup')->everyFiveMinutes();

// ── Pre-warm holiday cache at 05:00 every weekday ────────────────────────────
// Runs before students arrive so the external API is never called during scanning.
Schedule::command('attendance:holiday-check --days=14')
    ->weekdays()
    ->at('05:00')
    ->name('holiday-check')
    ->withoutOverlapping();

// ── Auto-mark absent at 09:00 weekdays (single-session Pagi) ─────────────────
// For multi-session schools, AutoMarkAbsent respects each session's auto_alpha_time
// internally, so this can run multiple times safely.
Schedule::command('attendance:auto-absent')
    ->weekdays()
    ->at('09:00')
    ->name('auto-absent-pagi')
    ->withoutOverlapping();

// Second run at 15:00 for Siang sessions
Schedule::command('attendance:auto-absent')
    ->weekdays()
    ->at('15:00')
    ->name('auto-absent-siang')
    ->withoutOverlapping();

// ── Auto-checkout at 13:05 (after Pagi ends) and 18:05 (after Siang ends) ────
Schedule::command('attendance:auto-checkout')
    ->weekdays()
    ->at('13:05')
    ->name('auto-checkout-pagi')
    ->withoutOverlapping();

Schedule::command('attendance:auto-checkout')
    ->weekdays()
    ->at('18:05')
    ->name('auto-checkout-siang')
    ->withoutOverlapping();

// ── Late-sync at 08:00 weekdays ───────────────────────────────────────────────
// Recalculates terlambat/hadir after possible session config changes overnight.
Schedule::command('attendance:late-sync')
    ->weekdays()
    ->at('08:00')
    ->name('late-sync')
    ->withoutOverlapping();
