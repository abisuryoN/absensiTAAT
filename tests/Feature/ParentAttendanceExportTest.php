<?php

namespace Tests\Feature;

use App\Models\AttendanceGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class ParentAttendanceExportTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        $this->createTestMasterData();
    }

    public function test_parent_can_export_daily_excel_for_their_own_child(): void
    {
        $data = $this->createParentWithStudent('Parent A', '2000000000000001', 'Student A', '1001', 'BC1001');
        
        AttendanceGate::create([
            'student_id' => $data['student']->id,
            'academic_year_id' => 1,
            'semester_id' => 1,
            'date' => now(),
            'status' => 'hadir',
            'time_in' => '07:00:00',
        ]);

        $response = $this->actingAs($data['parent_user'])
            ->get(route('parent.rekap_harian.excel', [
                'student_id' => $data['student']->id,
                'from' => now()->format('Y-m-d'),
                'to' => now()->format('Y-m-d')
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_parent_can_export_daily_pdf_for_their_own_child(): void
    {
        $data = $this->createParentWithStudent('Parent B', '2000000000000002', 'Student B', '1002', 'BC1002');
        
        AttendanceGate::create([
            'student_id' => $data['student']->id,
            'academic_year_id' => 1,
            'semester_id' => 1,
            'date' => now(),
            'status' => 'hadir',
            'time_in' => '07:00:00',
        ]);

        $response = $this->actingAs($data['parent_user'])
            ->get(route('parent.rekap_harian.pdf', [
                'student_id' => $data['student']->id,
                'from' => now()->format('Y-m-d'),
                'to' => now()->format('Y-m-d')
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_parent_can_export_monthly_excel_for_their_own_child(): void
    {
        $data = $this->createParentWithStudent('Parent C', '2000000000000003', 'Student C', '1003', 'BC1003');
        
        AttendanceGate::create([
            'student_id' => $data['student']->id,
            'academic_year_id' => 1,
            'semester_id' => 1,
            'date' => now(),
            'status' => 'hadir',
            'time_in' => '07:00:00',
        ]);

        $response = $this->actingAs($data['parent_user'])
            ->get(route('parent.rekap_bulanan.excel', [
                'student_id' => $data['student']->id,
                'month' => now()->month,
                'year' => now()->year
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_parent_can_export_monthly_pdf_for_their_own_child(): void
    {
        $data = $this->createParentWithStudent('Parent D', '2000000000000004', 'Student D', '1004', 'BC1004');
        
        AttendanceGate::create([
            'student_id' => $data['student']->id,
            'academic_year_id' => 1,
            'semester_id' => 1,
            'date' => now(),
            'status' => 'hadir',
            'time_in' => '07:00:00',
        ]);

        $response = $this->actingAs($data['parent_user'])
            ->get(route('parent.rekap_bulanan.pdf', [
                'student_id' => $data['student']->id,
                'month' => now()->month,
                'year' => now()->year
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_parent_cannot_export_other_parent_child_data(): void
    {
        $dataA = $this->createParentWithStudent('Parent A', '2000000000000005', 'Student A', '1005', 'BC1005');
        $dataB = $this->createParentWithStudent('Parent B', '2000000000000006', 'Student B', '1006', 'BC1006');
        
        AttendanceGate::create([
            'student_id' => $dataB['student']->id,
            'academic_year_id' => 1,
            'semester_id' => 1,
            'date' => now(),
            'status' => 'hadir',
            'time_in' => '07:00:00',
        ]);

        // Parent A tries to export Student B's data
        $response = $this->actingAs($dataA['parent_user'])
            ->get(route('parent.rekap_harian.excel', [
                'student_id' => $dataB['student']->id,
                'from' => now()->format('Y-m-d'),
                'to' => now()->format('Y-m-d')
            ]));

        $response->assertStatus(403);
    }
}
