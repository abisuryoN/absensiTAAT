<?php

namespace Tests\Feature;

use App\Models\AttendanceGate;
use App\Notifications\AttendanceAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class AttendanceNotificationTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        $this->createTestMasterData();
    }

    public function test_parent_receives_notification_when_student_becomes_late(): void
    {
        Notification::fake();
        $data = $this->createParentWithStudent('Parent A', '1000000000000001', 'Student A', '3001', 'BC3001');
        
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

    public function test_parent_receives_notification_when_student_absent(): void
    {
        Notification::fake();
        $data = $this->createParentWithStudent('Parent B', '1000000000000002', 'Student B', '3002', 'BC3002');
        
        $attendance = AttendanceGate::create([
            'student_id' => $data['student']->id,
            'academic_year_id' => 1,
            'semester_id' => 1,
            'date' => now(),
            'status' => 'hadir',
            'time_in' => '07:00:00',
        ]);

        $attendance->update(['status' => 'tidak_hadir']);

        Notification::assertSentTo($data['parent_user'], AttendanceAlert::class);
    }

    public function test_parent_does_not_receive_notification_for_other_statuses(): void
    {
        Notification::fake();
        $data = $this->createParentWithStudent('Parent C', '1000000000000003', 'Student C', '3003', 'BC3003');
        
        $attendance = AttendanceGate::create([
            'student_id' => $data['student']->id,
            'academic_year_id' => 1,
            'semester_id' => 1,
            'date' => now(),
            'status' => 'terlambat',
            'time_in' => '08:00:00',
        ]);

        $attendance->update(['status' => 'hadir']);

        Notification::assertNothingSent();
    }

    public function test_duplicate_notifications_are_prevented(): void
    {
        $data = $this->createParentWithStudent('Parent D', '1000000000000004', 'Student D', '3004', 'BC3004');
        
        $attendance = AttendanceGate::create([
            'student_id' => $data['student']->id,
            'academic_year_id' => 1,
            'semester_id' => 1,
            'date' => now(),
            'status' => 'hadir',
            'time_in' => '07:00:00',
        ]);

        $attendance->update(['status' => 'terlambat']);
        $firstCount = $data['parent_user']->notifications()->count();

        $attendance->update(['status' => 'hadir']);
        $attendance->update(['status' => 'terlambat']);

        $secondCount = $data['parent_user']->notifications()->count();
        $this->assertEquals($firstCount, $secondCount);
    }
}
