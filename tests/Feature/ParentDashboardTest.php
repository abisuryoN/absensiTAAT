<?php

namespace Tests\Feature;

use App\Models\AttendanceGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class ParentDashboardTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        $this->createTestMasterData();
    }

    public function test_statistics_correctly_map_alpha_to_tidak_hadir(): void
    {
        $data = $this->createParentWithStudent('Parent Test', '4000000000000001', 'Student Test', '4001', 'BC4001');
        
        $records = [
            'hadir' => '07:00:00',
            'terlambat' => '08:00:00',
            'tidak_hadir' => '00:00:00',
            'izin' => '00:00:00',
            'sakit' => '00:00:00',
        ];

        $dayOffset = 0;
        foreach ($records as $status => $time) {
            AttendanceGate::create([
                'student_id' => $data['student']->id,
                'academic_year_id' => 1,
                'semester_id' => 1,
                'date' => now()->subDays($dayOffset++),
                'status' => $status,
                'time_in' => $time,
            ]);
        }

        $response = $this->actingAs($data['parent_user'])
            ->get(route('parent.dashboard'));

        $response->assertStatus(200);
        $summary = $response->viewData('summary');
        
        $this->assertEquals(1, $summary['tidak_hadir']);
        $this->assertEquals(5, $summary['total']);
    }

    public function test_parent_can_only_view_their_own_child_data(): void
    {
        $dataA = $this->createParentWithStudent('Parent A', '4000000000000002', 'Student A', '4002', 'BC4002');
        $dataB = $this->createParentWithStudent('Parent B', '4000000000000003', 'Student B', '4003', 'BC4003');

        $response = $this->actingAs($dataA['parent_user'])
            ->get(route('parent.student.data', ['student' => $dataA['student']->id]));
        $response->assertStatus(200);

        $response = $this->actingAs($dataA['parent_user'])
            ->get(route('parent.student.data', ['student' => $dataB['student']->id]));
        $response->assertStatus(403);
    }
}
