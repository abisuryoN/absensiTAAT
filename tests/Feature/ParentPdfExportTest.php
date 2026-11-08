<?php

namespace Tests\Feature;

use App\Models\AttendanceGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class ParentPdfExportTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        $this->createTestMasterData();
    }

    public function test_parent_can_export_daily_attendance_to_pdf(): void
    {
        $data = $this->createParentWithStudent('Parent Test', '4000000000000010', 'Student Test', '4010', 'BC4010');
        
        // Create test attendance records
        for ($i = 0; $i < 5; $i++) {
            AttendanceGate::create([
                'student_id' => $data['student']->id,
                'academic_year_id' => 1,
                'semester_id' => 1,
                'date' => now()->subDays($i),
                'status' => 'hadir',
                'time_in' => '07:30:00',
            ]);
        }

        $response = $this->actingAs($data['parent_user'])
            ->get(route('parent.rekap_harian.pdf', [
                'student_id' => $data['student']->id,
                'from' => now()->subDays(10)->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
            ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        
        // Verify PDF signature
        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF', $content);
    }

    public function test_parent_cannot_export_other_student_daily_pdf(): void
    {
        $dataA = $this->createParentWithStudent('Parent A', '4000000000000011', 'Student A', '4011', 'BC4011');
        $dataB = $this->createParentWithStudent('Parent B', '4000000000000012', 'Student B', '4012', 'BC4012');

        $response = $this->actingAs($dataA['parent_user'])
            ->get(route('parent.rekap_harian.pdf', [
                'student_id' => $dataB['student']->id,
                'from' => now()->subDays(10)->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
            ]));

        $response->assertStatus(403);
    }

    public function test_parent_can_export_monthly_attendance_to_pdf(): void
    {
        $data = $this->createParentWithStudent('Parent Test', '4000000000000013', 'Student Test', '4013', 'BC4013');
        
        // Create test attendance records for multiple months
        for ($month = 1; $month <= 3; $month++) {
            for ($day = 1; $day <= 5; $day++) {
                AttendanceGate::create([
                    'student_id' => $data['student']->id,
                    'academic_year_id' => 1,
                    'semester_id' => 1,
                    'date' => now()->setMonth($month)->setDay($day),
                    'status' => 'hadir',
                    'time_in' => '07:30:00',
                ]);
            }
        }

        $response = $this->actingAs($data['parent_user'])
            ->get(route('parent.rekap_bulanan.pdf', [
                'student_id' => $data['student']->id,
                'year' => now()->year,
            ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        
        // Verify PDF signature
        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF', $content);
    }

    public function test_parent_cannot_export_other_student_monthly_pdf(): void
    {
        $dataA = $this->createParentWithStudent('Parent A', '4000000000000014', 'Student A', '4014', 'BC4014');
        $dataB = $this->createParentWithStudent('Parent B', '4000000000000015', 'Student B', '4015', 'BC4015');

        $response = $this->actingAs($dataA['parent_user'])
            ->get(route('parent.rekap_bulanan.pdf', [
                'student_id' => $dataB['student']->id,
                'year' => now()->year,
            ]));

        $response->assertStatus(403);
    }
}
