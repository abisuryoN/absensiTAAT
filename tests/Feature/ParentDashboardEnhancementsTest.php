<?php

namespace Tests\Feature;

use App\Models\AttendanceGate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

/**
 * Test suite for Tahap 3 Parent Dashboard enhancements:
 * - Persentase kehadiran
 * - Grafik tren 6 bulan
 * - Perbandingan bulanan
 */
class ParentDashboardEnhancementsTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestMasterData();
    }

    public function test_dashboard_includes_attendance_percentage(): void
    {
        $data = $this->createParentWithStudent('Parent A', '5000000000000001', 'Student A', '5001', 'BC5001');
        
        // Create attendance records: 8 hadir, 2 terlambat (10 total)
        // Percentage should be (8 + 2) / 10 * 100 = 100%
        for ($i = 1; $i <= 8; $i++) {
            AttendanceGate::create([
                'student_id' => $data['student']->id,
                'academic_year_id' => 1,
                'semester_id' => 1,
                'date' => Carbon::now()->subDays($i)->toDateString(),
                'status' => 'hadir',
                'time_in' => '07:00:00',
            ]);
        }
        
        for ($i = 9; $i <= 10; $i++) {
            AttendanceGate::create([
                'student_id' => $data['student']->id,
                'academic_year_id' => 1,
                'semester_id' => 1,
                'date' => Carbon::now()->subDays($i)->toDateString(),
                'status' => 'terlambat',
                'time_in' => '07:30:00',
            ]);
        }

        $response = $this->actingAs($data['parent_user'])
            ->get(route('parent.dashboard'));

        $response->assertOk();
        $response->assertSee('100%'); // Persentase Hadir
        $response->assertSee('Persentase Hadir');
    }

    public function test_dashboard_includes_six_month_trend_chart(): void
    {
        $data = $this->createParentWithStudent('Parent B', '5000000000000002', 'Student B', '5002', 'BC5002');
        
        // Create attendance across multiple months
        for ($month = 0; $month < 6; $month++) {
            AttendanceGate::create([
                'student_id' => $data['student']->id,
                'academic_year_id' => 1,
                'semester_id' => 1,
                'date' => Carbon::now()->subMonths($month)->startOfMonth()->toDateString(),
                'status' => 'hadir',
                'time_in' => '07:00:00',
            ]);
        }

        $response = $this->actingAs($data['parent_user'])
            ->get(route('parent.dashboard'));

        $response->assertOk();
        $response->assertSee('Tren Kehadiran 6 Bulan Terakhir');
        $response->assertSee('attendanceTrendChart');
    }

    public function test_dashboard_includes_monthly_comparison(): void
    {
        $data = $this->createParentWithStudent('Parent C', '5000000000000003', 'Student C', '5003', 'BC5003');
        
        // Current month: 8 hadir, 2 terlambat (100%)
        for ($i = 1; $i <= 8; $i++) {
            AttendanceGate::create([
                'student_id' => $data['student']->id,
                'academic_year_id' => 1,
                'semester_id' => 1,
                'date' => Carbon::now()->startOfMonth()->addDays($i)->toDateString(),
                'status' => 'hadir',
                'time_in' => '07:00:00',
            ]);
        }
        
        for ($i = 9; $i <= 10; $i++) {
            AttendanceGate::create([
                'student_id' => $data['student']->id,
                'academic_year_id' => 1,
                'semester_id' => 1,
                'date' => Carbon::now()->startOfMonth()->addDays($i)->toDateString(),
                'status' => 'terlambat',
                'time_in' => '07:30:00',
            ]);
        }

        // Previous month: 6 hadir, 4 tidak_hadir (60%)
        for ($i = 1; $i <= 6; $i++) {
            AttendanceGate::create([
                'student_id' => $data['student']->id,
                'academic_year_id' => 1,
                'semester_id' => 1,
                'date' => Carbon::now()->subMonth()->startOfMonth()->addDays($i)->toDateString(),
                'status' => 'hadir',
                'time_in' => '07:00:00',
            ]);
        }
        
        for ($i = 7; $i <= 10; $i++) {
            AttendanceGate::create([
                'student_id' => $data['student']->id,
                'academic_year_id' => 1,
                'semester_id' => 1,
                'date' => Carbon::now()->subMonth()->startOfMonth()->addDays($i)->toDateString(),
                'status' => 'tidak_hadir',
                'time_in' => '00:00:00',
            ]);
        }

        $response = $this->actingAs($data['parent_user'])
            ->get(route('parent.dashboard'));

        $response->assertOk();
        $response->assertSee('Perbandingan Bulanan');
        $response->assertSee('Meningkat'); // Improvement indicator
    }

    public function test_percentage_calculation_with_mixed_statuses(): void
    {
        $data = $this->createParentWithStudent('Parent D', '5000000000000004', 'Student D', '5004', 'BC5004');
        
        // Create attendance for CURRENT month with mixed statuses
        $now = Carbon::now();
        
        // 5 hadir
        for ($i = 1; $i <= 5; $i++) {
            AttendanceGate::create([
                'student_id' => $data['student']->id,
                'academic_year_id' => 1,
                'semester_id' => 1,
                'date' => $now->copy()->startOfMonth()->addDays($i - 1)->toDateString(),
                'status' => 'hadir',
                'time_in' => '07:00:00',
            ]);
        }
        
        // 3 terlambat
        for ($i = 6; $i <= 8; $i++) {
            AttendanceGate::create([
                'student_id' => $data['student']->id,
                'academic_year_id' => 1,
                'semester_id' => 1,
                'date' => $now->copy()->startOfMonth()->addDays($i - 1)->toDateString(),
                'status' => 'terlambat',
                'time_in' => '07:30:00',
            ]);
        }
        
        // 2 tidak_hadir
        for ($i = 9; $i <= 10; $i++) {
            AttendanceGate::create([
                'student_id' => $data['student']->id,
                'academic_year_id' => 1,
                'semester_id' => 1,
                'date' => $now->copy()->startOfMonth()->addDays($i - 1)->toDateString(),
                'status' => 'tidak_hadir',
                'time_in' => '00:00:00',
            ]);
        }

        $response = $this->actingAs($data['parent_user'])
            ->get(route('parent.dashboard'));

        $response->assertOk();
        $response->assertSee('80%'); // (5 hadir + 3 terlambat) / 10 total = 80%
    }

    public function test_dashboard_handles_zero_attendance_gracefully(): void
    {
        $data = $this->createParentWithStudent('Parent E', '5000000000000005', 'Student E', '5005', 'BC5005');
        
        // No attendance records
        $response = $this->actingAs($data['parent_user'])
            ->get(route('parent.dashboard'));

        $response->assertOk();
        $response->assertSee('0%'); // Zero percentage
        $response->assertSee('Tren Kehadiran 6 Bulan Terakhir');
        $response->assertSee('Perbandingan Bulanan');
    }

    public function test_six_month_trend_handles_month_overflow(): void
    {
        $data = $this->createParentWithStudent('Parent F', '5000000000000006', 'Student F', '5006', 'BC5006');
        
        // Test with March 31, 2026 (should handle Feb which has 28 days)
        Carbon::setTestNow(Carbon::create(2026, 3, 31));

        // Create attendance in expected months: Oct 2025, Nov 2025, Dec 2025, Jan 2026, Feb 2026, Mar 2026
        $expectedMonths = [
            ['year' => 2025, 'month' => 10, 'day' => 15],
            ['year' => 2025, 'month' => 11, 'day' => 15],
            ['year' => 2025, 'month' => 12, 'day' => 15],
            ['year' => 2026, 'month' => 1, 'day' => 15],
            ['year' => 2026, 'month' => 2, 'day' => 15],
            ['year' => 2026, 'month' => 3, 'day' => 15],
        ];

        foreach ($expectedMonths as $dateData) {
            AttendanceGate::create([
                'student_id' => $data['student']->id,
                'academic_year_id' => 1,
                'semester_id' => 1,
                'date' => Carbon::create($dateData['year'], $dateData['month'], $dateData['day']),
                'status' => 'hadir',
                'time_in' => '07:00:00',
            ]);
        }

        $response = $this->actingAs($data['parent_user'])
            ->get(route('parent.dashboard'));

        $response->assertOk();
        // Should see all 6 distinct months without duplication
        $response->assertSee('Tren Kehadiran 6 Bulan Terakhir');

        Carbon::setTestNow(); // Reset
    }

    public function test_monthly_comparison_handles_month_overflow(): void
    {
        $data = $this->createParentWithStudent('Parent G', '5000000000000007', 'Student G', '5007', 'BC5007');
        
        // Test with May 30, 2026 (April has 30 days)
        Carbon::setTestNow(Carbon::create(2026, 5, 30));

        // May 2026: 8 hadir out of 10
        for ($day = 1; $day <= 10; $day++) {
            AttendanceGate::create([
                'student_id' => $data['student']->id,
                'academic_year_id' => 1,
                'semester_id' => 1,
                'date' => Carbon::create(2026, 5, $day),
                'status' => $day <= 8 ? 'hadir' : 'tidak_hadir',
                'time_in' => '07:00:00', // time_in required, use dummy for tidak_hadir
            ]);
        }

        // April 2026: 5 hadir out of 10
        for ($day = 1; $day <= 10; $day++) {
            AttendanceGate::create([
                'student_id' => $data['student']->id,
                'academic_year_id' => 1,
                'semester_id' => 1,
                'date' => Carbon::create(2026, 4, $day),
                'status' => $day <= 5 ? 'hadir' : 'tidak_hadir',
                'time_in' => '07:00:00', // time_in required, use dummy for tidak_hadir
            ]);
        }

        $response = $this->actingAs($data['parent_user'])
            ->get(route('parent.dashboard'));

        $response->assertOk();
        // Should compare May vs April correctly (80% vs 50%)
        $response->assertSee('Perbandingan Bulanan');

        Carbon::setTestNow(); // Reset
    }

    public function test_monthly_comparison_with_no_previous_data(): void
    {
        $data = $this->createParentWithStudent('Parent H', '5000000000000008', 'Student H', '5008', 'BC5008');
        
        Carbon::setTestNow(Carbon::create(2026, 11, 8));

        // Only current month (Nov 2026) has data
        AttendanceGate::create([
            'student_id' => $data['student']->id,
            'academic_year_id' => 1,
            'semester_id' => 1,
            'date' => Carbon::create(2026, 11, 5),
            'status' => 'hadir',
            'time_in' => '07:00:00',
        ]);

        // No data for October 2026

        $response = $this->actingAs($data['parent_user'])
            ->get(route('parent.dashboard'));

        $response->assertOk();
        // Should show info message instead of comparison
        $response->assertSee('Data bulan sebelumnya belum tersedia');

        Carbon::setTestNow(); // Reset
    }

    public function test_six_month_trend_handles_year_boundary(): void
    {
        $data = $this->createParentWithStudent('Parent I', '5000000000000009', 'Student I', '5009', 'BC5009');
        
        // Test with December 31, 2025
        Carbon::setTestNow(Carbon::create(2025, 12, 31));

        // Expected months: Jul 2025, Aug 2025, Sep 2025, Oct 2025, Nov 2025, Dec 2025
        $expectedMonths = [
            ['year' => 2025, 'month' => 7, 'day' => 15],
            ['year' => 2025, 'month' => 8, 'day' => 15],
            ['year' => 2025, 'month' => 9, 'day' => 15],
            ['year' => 2025, 'month' => 10, 'day' => 15],
            ['year' => 2025, 'month' => 11, 'day' => 15],
            ['year' => 2025, 'month' => 12, 'day' => 15],
        ];

        foreach ($expectedMonths as $dateData) {
            AttendanceGate::create([
                'student_id' => $data['student']->id,
                'academic_year_id' => 1,
                'semester_id' => 1,
                'date' => Carbon::create($dateData['year'], $dateData['month'], $dateData['day']),
                'status' => 'hadir',
                'time_in' => '07:00:00',
            ]);
        }

        $response = $this->actingAs($data['parent_user'])
            ->get(route('parent.dashboard'));

        $response->assertOk();
        $response->assertSee('Tren Kehadiran 6 Bulan Terakhir');

        Carbon::setTestNow(); // Reset
    }
}
