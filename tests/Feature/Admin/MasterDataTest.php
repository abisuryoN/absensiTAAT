<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $teacherUser;
    protected User $studentUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Spatie roles
        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'guru']);
        Role::firstOrCreate(['name' => 'siswa']);

        // Create users
        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole('super_admin');

        $this->teacherUser = User::factory()->create(['is_active' => true]);
        $this->teacherUser->assignRole('guru');

        $this->studentUser = User::factory()->create(['is_active' => true]);
        $this->studentUser->assignRole('siswa');
    }

    public function test_unauthenticated_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_teacher_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
    }

    public function test_admin_can_create_academic_year(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.academic-years.store'), [
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.academic-years.index'));
        $this->assertDatabaseHas('academic_years', [
            'name' => '2026/2027',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_toggle_academic_year_active_status_exclusively(): void
    {
        // Create an existing active academic year
        $year1 = AcademicYear::create([
            'name' => '2024/2025',
            'start_date' => '2024-07-01',
            'end_date' => '2025-06-30',
            'is_active' => true,
        ]);

        // Create a new active year via controller
        $this->actingAs($this->admin)->post(route('admin.academic-years.store'), [
            'name' => '2025/2026',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => 1,
        ]);

        // Verify exclusivity: year1 should now be inactive, new year should be active
        $this->assertFalse((bool) $year1->fresh()->is_active);
        $this->assertDatabaseHas('academic_years', [
            'name' => '2025/2026',
            'is_active' => true,
        ]);
    }

    public function test_cannot_add_overlapping_schedule_for_same_teacher(): void
    {
        // Seed necessary models
        $year = AcademicYear::create(['name' => '2025/2026', 'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true]);
        $semester = Semester::create(['academic_year_id' => $year->id, 'name' => 'Ganjil', 'semester_number' => 1, 'start_date' => '2025-07-01', 'end_date' => '2025-12-31', 'is_active' => true]);
        $major = Major::create(['name' => 'MIPA', 'code' => 'MIPA', 'is_active' => true]);
        
        $teacher = Teacher::create(['user_id' => $this->teacherUser->id, 'name' => 'Guru Test', 'gender' => 'L', 'is_active' => true]);
        
        $class1 = SchoolClass::create(['academic_year_id' => $year->id, 'major_id' => $major->id, 'grade_level' => 10, 'name' => 'X MIPA 1', 'capacity' => 36, 'is_active' => true]);
        $class2 = SchoolClass::create(['academic_year_id' => $year->id, 'major_id' => $major->id, 'grade_level' => 10, 'name' => 'X MIPA 2', 'capacity' => 36, 'is_active' => true]);
        
        $subject = Subject::create(['name' => 'Matematika', 'code' => 'MTK', 'is_active' => true]);

        // Create first schedule for teacher in class1: 07:00 - 08:30
        Schedule::create([
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'class_id' => $class1->id,
            'day' => 'Senin',
            'start_time' => '07:00',
            'end_time' => '08:30',
            'is_active' => true,
        ]);

        // Try to add overlapping schedule for SAME teacher in class2: 08:00 - 09:30
        $response = $this->actingAs($this->admin)->post(route('admin.schedules.store'), [
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'class_id' => $class2->id,
            'day' => 'Senin',
            'start_time' => '08:00',
            'end_time' => '09:30',
            'is_active' => 1,
        ]);

        // Assert redirect back with error session flash
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('schedules', 1);
    }
}
