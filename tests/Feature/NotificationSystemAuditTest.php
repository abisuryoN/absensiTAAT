<?php

namespace Tests\Feature;

use App\Models\AttendanceGate;
use App\Models\DatabaseNotification;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class NotificationSystemAuditTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        $this->createTestMasterData();
    }

    public function test_laravel_notifications_table_exists(): void
    {
        $this->assertTrue(
            \Schema::hasTable('laravel_notifications'),
            'Table laravel_notifications harus ada'
        );
    }

    public function test_old_notifications_table_exists_for_whatsapp(): void
    {
        $this->assertTrue(
            \Schema::hasTable('notifications'),
            'Table notifications (WhatsApp) harus tetap ada'
        );
    }

    public function test_laravel_notifications_has_correct_structure(): void
    {
        $columns = \Schema::getColumnListing('laravel_notifications');
        
        $this->assertContains('id', $columns);
        $this->assertContains('type', $columns);
        $this->assertContains('notifiable_type', $columns);
        $this->assertContains('notifiable_id', $columns);
        $this->assertContains('data', $columns);
        $this->assertContains('read_at', $columns);
        $this->assertContains('created_at', $columns);
        $this->assertContains('updated_at', $columns);
    }

    public function test_old_notifications_table_has_whatsapp_structure(): void
    {
        $columns = \Schema::getColumnListing('notifications');
        
        $this->assertContains('student_id', $columns);
        $this->assertContains('parent_id', $columns);
        $this->assertContains('channel', $columns);
        $this->assertContains('phone_number', $columns);
        $this->assertContains('message', $columns);
        $this->assertContains('status', $columns);
    }

    public function test_user_notifications_use_laravel_notifications_table(): void
    {
        $data = $this->createParentWithStudent('Parent Test', '4000000000000020', 'Student Test', '4020', 'BC4020');
        
        AttendanceGate::create([
            'student_id' => $data['student']->id,
            'academic_year_id' => 1,
            'semester_id' => 1,
            'date' => now(),
            'status' => 'terlambat',
            'time_in' => '08:00:00',
        ]);

        $notifications = $data['parent_user']->notifications;
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $notifications);
        
        if ($notifications->count() > 0) {
            $this->assertInstanceOf(DatabaseNotification::class, $notifications->first());
            $this->assertEquals('laravel_notifications', $notifications->first()->getTable());
        }
    }

    public function test_notification_model_uses_notifications_table(): void
    {
        $notification = new Notification();
        $this->assertEquals('notifications', $notification->getTable());
    }

    public function test_database_notification_model_uses_laravel_notifications_table(): void
    {
        $notification = new DatabaseNotification();
        $this->assertEquals('laravel_notifications', $notification->getTable());
    }

    public function test_attendance_notification_stores_in_laravel_notifications(): void
    {
        $data = $this->createParentWithStudent('Parent Test', '4000000000000021', 'Student Test', '4021', 'BC4021');
        
        // Clear any existing notifications
        \DB::table('laravel_notifications')->truncate();
        
        // Create initial record with 'hadir' status
        $attendance = AttendanceGate::create([
            'student_id' => $data['student']->id,
            'academic_year_id' => 1,
            'semester_id' => 1,
            'date' => now(),
            'status' => 'hadir',
            'time_in' => '07:00:00',
        ]);

        // Update status to trigger observer (observer only watches 'updated' event)
        $attendance->update(['status' => 'tidak_hadir']);

        $count = \DB::table('laravel_notifications')
            ->where('notifiable_type', get_class($data['parent_user']))
            ->where('notifiable_id', $data['parent_user']->id)
            ->count();
        
        $this->assertEquals(1, $count, 'Notifikasi harus tersimpan di laravel_notifications');
    }
}
