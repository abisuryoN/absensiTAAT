<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\User;
use App\Models\Holiday;
use App\Models\Setting;
use App\Models\QrToken;
use App\Models\AttendanceGate;
use App\Services\AttendanceGateService;
use App\Jobs\SendWhatsAppNotificationJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceGateTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Student $student;
    protected AttendanceGateService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed Roles
        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'siswa']);

        // Create Admin User
        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole('super_admin');

        // Create Academic structures
        $year = AcademicYear::create(['name' => '2025/2026', 'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true]);
        $semester = Semester::create(['academic_year_id' => $year->id, 'name' => 'Ganjil', 'semester_number' => 1, 'start_date' => '2025-07-01', 'end_date' => '2025-12-31', 'is_active' => true]);
        $major = Major::create(['name' => 'RPL', 'code' => 'RPL', 'is_active' => true]);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'major_id' => $major->id, 'grade_level' => 10, 'name' => 'X RPL 1', 'capacity' => 36, 'is_active' => true]);

        // Create Parent & Student
        $parent = StudentParent::create(['name' => 'Bapak Budi', 'phone' => '08123456789', 'relationship' => 'Ayah']);
        $studentUser = User::factory()->create(['is_active' => true]);
        $studentUser->assignRole('siswa');

        $this->student = Student::create([
            'user_id' => $studentUser->id,
            'parent_id' => $parent->id,
            'class_id' => $class->id,
            'nis' => '12345',
            'name' => 'Andi Siswa',
            'gender' => 'L',
            'barcode_id' => 'ANDI-12345',
            'is_active' => true,
        ]);

        $this->service = new AttendanceGateService();

        // Standard configuration seeding
        Setting::updateOrCreate(['key' => 'school_start_time'], ['group' => 'attendance', 'value' => '06:30', 'type' => 'string']);
        Setting::updateOrCreate(['key' => 'late_threshold_minutes'], ['group' => 'attendance', 'value' => '15', 'type' => 'integer']);
        Setting::updateOrCreate(['key' => 'whatsapp_enabled'], ['group' => 'whatsapp', 'value' => 'true', 'type' => 'boolean']);
    }

    public function test_barcode_scan_records_attendance_and_dispatches_notification(): void
    {
        Queue::fake();

        // Execute scan
        $attendance = $this->service->processBarcodeScan('ANDI-12345', $this->admin->id);

        $this->assertInstanceOf(AttendanceGate::class, $attendance);
        $this->assertEquals($this->student->id, $attendance->student_id);
        $this->assertDatabaseHas('attendance_gates', [
            'student_id' => $this->student->id,
            'method' => 'barcode',
        ]);

        // Assert notification job was queued
        Queue::assertPushed(SendWhatsAppNotificationJob::class, function ($job) use ($attendance) {
            return $job->attendance->id === $attendance->id;
        });
    }

    public function test_cannot_scan_twice_on_same_day(): void
    {
        $this->service->processBarcodeScan('ANDI-12345', $this->admin->id);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("sudah melakukan absensi hari ini");

        $this->service->processBarcodeScan('ANDI-12345', $this->admin->id);
    }

    public function test_cannot_scan_on_holidays(): void
    {
        // Add holiday for today
        Holiday::create([
            'academic_year_id' => AcademicYear::active()->first()->id,
            'name' => 'Hari Libur Uji Coba',
            'date' => Carbon::today()->format('Y-m-d'),
            'type' => 'national',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("adalah hari libur sekolah");

        $this->service->processBarcodeScan('ANDI-12345', $this->admin->id);
    }

    public function test_status_mapping_on_time_versus_late(): void
    {
        // 06:30 school start + 15 min tolerance = 06:45 threshold.
        
        // Test On-time
        $this->assertEquals('hadir', $this->service->getStatusByTime('06:40:00'));
        $this->assertEquals('hadir', $this->service->getStatusByTime('06:45:00'));

        // Test Late
        $this->assertEquals('terlambat', $this->service->getStatusByTime('06:46:00'));
        $this->assertEquals('terlambat', $this->service->getStatusByTime('07:00:00'));
    }

    public function test_qr_code_scan_validates_and_invalidates_dynamic_token(): void
    {
        $token = 'random-dynamic-hex-token-32-bytes-long';
        $qrToken = QrToken::create([
            'student_id' => $this->student->id,
            'token' => $token,
            'expires_at' => Carbon::now()->addSeconds(30),
            'is_used' => false,
        ]);

        $attendance = $this->service->processQrScan($token, $this->admin->id);

        $this->assertDatabaseHas('attendance_gates', [
            'student_id' => $this->student->id,
            'method' => 'qr_code',
        ]);

        $this->assertTrue((bool) $qrToken->fresh()->is_used);

        // Try to scan again with same used token should fail
        $this->expectException(\Exception::class);
        $this->service->processQrScan($token, $this->admin->id);
    }

    public function test_auto_alpha_scheduler_marks_missing_students(): void
    {
        // Total active students: 1 (this->student). Currently no attendance gate records.
        $count = $this->service->markAbsentStudents();

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('attendance_gates', [
            'student_id' => $this->student->id,
            'status' => 'alpha',
        ]);
    }
}
