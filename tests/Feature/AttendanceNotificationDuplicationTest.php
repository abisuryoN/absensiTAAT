<?php

namespace Tests\Feature;

use App\Models\AttendanceGate;
use App\Notifications\AttendanceAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class AttendanceNotificationDuplicationTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        $this->createTestMasterData();
    }

    public function test_single_event_does_not_produce_duplicate_notification(): void
    {
        $data = $this->createParentWithStudent('Parent A', '5000000000000001', 'Student A', '5001', 'BC5001');
        
        // Create attendance record with 'hadir' status
        $attendance = AttendanceGate::create([
            'student_id' => $data['student']->id,
            'academic_year_id' => 1,
            'semester_id' => 1,
            'date' => now(),
            'status' => 'hadir',
            'time_in' => '07:00:00',
        ]);

        // First update to 'terlambat' - should trigger notification
        $attendance->update(['status' => 'terlambat']);
        $firstCount = $data['parent_user']->notifications()->count();
        $this->assertEquals(1, $firstCount, 'First update should create notification');

        // Update back to 'hadir'
        $attendance->update(['status' => 'hadir']);
        
        // Second update to 'terlambat' with SAME record - should NOT create duplicate
        $attendance->update(['status' => 'terlambat']);
        $secondCount = $data['parent_user']->notifications()->count();
        
        $this->assertEquals(1, $secondCount, 'Same event updated twice should not create duplicate notification');
    }

    public function test_database_enforces_one_attendance_per_student_per_day(): void
    {
        $data = $this->createParentWithStudent('Parent B', '5000000000000002', 'Student B', '5002', 'BC5002');
        
        // First attendance record for today
        AttendanceGate::create([
            'student_id' => $data['student']->id,
            'academic_year_id' => 1,
            'semester_id' => 1,
            'date' => now(),
            'status' => 'hadir',
            'time_in' => '07:00:00',
        ]);
        
        // Attempt to create second attendance record for same student, same day
        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->expectExceptionMessage('UNIQUE constraint failed');
        
        AttendanceGate::create([
            'student_id' => $data['student']->id,
            'academic_year_id' => 1,
            'semester_id' => 1,
            'date' => now(),
            'status' => 'hadir',
            'time_in' => '07:05:00',
        ]);
    }

    public function test_column_changes_without_status_change_do_not_trigger_notification(): void
    {
        Notification::fake();
        $data = $this->createParentWithStudent('Parent C', '5000000000000003', 'Student C', '5003', 'BC5003');
        
        $attendance = AttendanceGate::create([
            'student_id' => $data['student']->id,
            'academic_year_id' => 1,
            'semester_id' => 1,
            'date' => now(),
            'status' => 'terlambat',
            'time_in' => '08:00:00',
        ]);

        // Update time_in without changing status
        $attendance->update(['time_in' => '08:15:00']);
        
        Notification::assertNothingSent();
    }

    public function test_status_change_from_hadir_to_terlambat_triggers_notification(): void
    {
        Notification::fake();
        $data = $this->createParentWithStudent('Parent D', '5000000000000004', 'Student D', '5004', 'BC5004');
        
        $attendance = AttendanceGate::create([
            'student_id' => $data['student']->id,
            'academic_year_id' => 1,
            'semester_id' => 1,
            'date' => now(),
            'status' => 'hadir',
            'time_in' => '07:00:00',
        ]);

        $attendance->update(['status' => 'terlambat']);
        
        Notification::assertSentTo($data['parent_user'], AttendanceAlert::class);
    }

    public function test_status_change_from_terlambat_to_tidak_hadir_triggers_new_notification(): void
    {
        $data = $this->createParentWithStudent('Parent E', '5000000000000005', 'Student E', '5005', 'BC5005');
        
        $attendance = AttendanceGate::create([
            'student_id' => $data['student']->id,
            'academic_year_id' => 1,
            'semester_id' => 1,
            'date' => now(),
            'status' => 'hadir',
            'time_in' => '07:00:00',
        ]);

        // First change: hadir -> terlambat
        $attendance->update(['status' => 'terlambat']);
        $firstCount = $data['parent_user']->notifications()->count();
        
        // Second change: terlambat -> tidak_hadir (different status, different message)
        $attendance->update(['status' => 'tidak_hadir']);
        $secondCount = $data['parent_user']->notifications()->count();
        
        $this->assertEquals(1, $firstCount, 'First status change should create notification');
        $this->assertEquals(2, $secondCount, 'Different status change should create new notification');
    }

    public function test_same_status_on_different_days_produces_separate_notifications(): void
    {
        $data = $this->createParentWithStudent('Parent F', '5000000000000006', 'Student F', '5006', 'BC5006');
        
        // Day 1
        $attendance1 = AttendanceGate::create([
            'student_id' => $data['student']->id,
            'academic_year_id' => 1,
            'semester_id' => 1,
            'date' => now()->subDay(),
            'status' => 'hadir',
            'time_in' => '07:00:00',
        ]);
        $attendance1->update(['status' => 'terlambat']);
        
        // Day 2
        $attendance2 = AttendanceGate::create([
            'student_id' => $data['student']->id,
            'academic_year_id' => 1,
            'semester_id' => 1,
            'date' => now(),
            'status' => 'hadir',
            'time_in' => '07:00:00',
        ]);
        $attendance2->update(['status' => 'terlambat']);
        
        $totalCount = $data['parent_user']->notifications()->count();
        $this->assertEquals(2, $totalCount, 'Same status on different days should create separate notifications');
    }
}
