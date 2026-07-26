<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceGate;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WeeklyAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $semester = Semester::where('is_active', true)->first();
        
        if (!$academicYear || !$semester) {
            $this->command->error('No active academic year or semester found');
            return;
        }
        
        $students = Student::where('is_active', true)->limit(50)->pluck('id')->toArray();
        
        if (empty($students)) {
            $this->command->error('No active students found');
            return;
        }
        
        $startDate = Carbon::now()->subDays(6);
        $inserted = 0;
        
        for ($i = 0; $i <= 6; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            
            // Check if data already exists for this date
            $existingCount = DB::table('attendance_gates')->whereDate('date', $date)->count();
            if ($existingCount > 0) {
                $this->command->warn("Skipping {$date} - already has {$existingCount} records");
                continue;
            }
            
            foreach ($students as $studentId) {
                $statuses = ['hadir', 'hadir', 'hadir', 'terlambat', 'sakit', 'izin', 'tidak_hadir'];
                $status = $statuses[array_rand($statuses)];
                $time = $status === 'terlambat' ? '07:45:00' : '07:15:00';
                
                DB::table('attendance_gates')->insert([
                    'student_id' => $studentId,
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $semester->id,
                    'date' => $date,
                    'time_in' => $time,
                    'status' => $status,
                    'method' => 'qr_code',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $inserted++;
            }
            
            $this->command->info("Inserted " . count($students) . " records for {$date}");
        }
        
        $this->command->info("Total inserted: {$inserted} records");
    }
}