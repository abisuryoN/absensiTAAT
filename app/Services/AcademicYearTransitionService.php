<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Major;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AcademicYearTransitionService
{
    /**
     * Get students ready for Grade 10 → 11 transition (need major selection)
     */
    public function getGrade10StudentsForMajorSelection(AcademicYear $currentYear): array
    {
        $students = Student::whereHas('schoolClass', function ($query) use ($currentYear) {
            $query->where('academic_year_id', $currentYear->id)
                  ->where('grade_level', 10);
        })
        ->with(['schoolClass', 'user'])
        ->orderBy('name')
        ->get();

        return [
            'students' => $students,
            'majors' => Major::where('is_active', true)->orderBy('name')->get(),
        ];
    }

    /**
     * Get available Grade 11 classes for a specific major
     */
    public function getGrade11ClassesByMajor(AcademicYear $newYear, int $majorId): array
    {
        return SchoolClass::where('academic_year_id', $newYear->id)
            ->where('grade_level', 11)
            ->where('major_id', $majorId)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    /**
     * Process Grade 10 → 11 transition with major selection
     */
    public function processGrade10To11Transition(array $assignments, AcademicYear $newYear): array
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        DB::beginTransaction();
        try {
            foreach ($assignments as $assignment) {
                $student = Student::find($assignment['student_id']);
                $targetClass = SchoolClass::find($assignment['class_id']);
                $major = Major::find($assignment['major_id']);

                if (!$student || !$targetClass || !$major) {
                    $results['failed'][] = [
                        'student_id' => $assignment['student_id'],
                        'reason' => 'Student, class, or major not found',
                    ];
                    continue;
                }

                // Validate that target class is Grade 11 with the correct major
                if ($targetClass->grade_level != 11 || $targetClass->major_id != $major->id) {
                    $results['failed'][] = [
                        'student_id' => $student->id,
                        'student_name' => $student->name,
                        'reason' => 'Target class mismatch with selected major',
                    ];
                    continue;
                }

                // Update student's class
                $student->class_id = $targetClass->id;
                $student->save();

                $results['success'][] = [
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'major' => $major->name,
                    'class' => $targetClass->name,
                ];
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Grade 10→11 transition failed: ' . $e->getMessage());
            throw $e;
        }

        return $results;
    }

    /**
     * Get students ready for Grade 11 → 12 transition (auto-mapping)
     */
    public function getGrade11StudentsWithAutoMapping(AcademicYear $currentYear, AcademicYear $newYear): array
    {
        $students = Student::whereHas('schoolClass', function ($query) use ($currentYear) {
            $query->where('academic_year_id', $currentYear->id)
                  ->where('grade_level', 11);
        })
        ->with(['schoolClass.major', 'user'])
        ->orderBy('name')
        ->get();

        $mappings = [];
        foreach ($students as $student) {
            $currentClass = $student->schoolClass;
            $majorId = $currentClass->major_id;

            // Auto-find Grade 12 class with the same major
            $suggestedClass = SchoolClass::where('academic_year_id', $newYear->id)
                ->where('grade_level', 12)
                ->where('major_id', $majorId)
                ->first();

            $mappings[] = [
                'student' => $student,
                'current_class' => $currentClass,
                'suggested_class' => $suggestedClass,
                'major' => $currentClass->major,
            ];
        }

        return [
            'mappings' => $mappings,
            'grade12_classes' => SchoolClass::where('academic_year_id', $newYear->id)
                ->where('grade_level', 12)
                ->with('major')
                ->orderBy('major_id')
                ->orderBy('name')
                ->get(),
        ];
    }

    /**
     * Process Grade 11 → 12 transition
     */
    public function processGrade11To12Transition(array $assignments, AcademicYear $newYear): array
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        DB::beginTransaction();
        try {
            foreach ($assignments as $assignment) {
                $student = Student::find($assignment['student_id']);
                $targetClass = SchoolClass::find($assignment['class_id']);

                if (!$student || !$targetClass) {
                    $results['failed'][] = [
                        'student_id' => $assignment['student_id'],
                        'reason' => 'Student or class not found',
                    ];
                    continue;
                }

                // Validate that target class is Grade 12
                if ($targetClass->grade_level != 12) {
                    $results['failed'][] = [
                        'student_id' => $student->id,
                        'student_name' => $student->name,
                        'reason' => 'Target class is not Grade 12',
                    ];
                    continue;
                }

                // Update student's class
                $student->class_id = $targetClass->id;
                $student->save();

                $results['success'][] = [
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'class' => $targetClass->name,
                ];
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Grade 11→12 transition failed: ' . $e->getMessage());
            throw $e;
        }

        return $results;
    }

    /**
     * Get students ready for Grade 12 → Graduate transition
     */
    public function getGrade12StudentsForGraduation(AcademicYear $currentYear): array
    {
        $students = Student::whereHas('schoolClass', function ($query) use ($currentYear) {
            $query->where('academic_year_id', $currentYear->id)
                  ->where('grade_level', 12);
        })
        ->with(['schoolClass.major', 'user'])
        ->orderBy('name')
        ->get();

        $mappings = [];
        foreach ($students as $student) {
            $mappings[] = [
                'student' => $student,
                'current_class' => $student->schoolClass,
                'suggested_status' => 'Lulus',
                'can_repeat' => true, // Admin can choose to keep student in Grade 12
            ];
        }

        return [
            'mappings' => $mappings,
            'grade12_classes' => SchoolClass::where('academic_year_id', $currentYear->id)
                ->where('grade_level', 12)
                ->with('major')
                ->orderBy('major_id')
                ->orderBy('name')
                ->get(),
        ];
    }

    /**
     * Process Grade 12 → Graduate transition
     */
    public function processGrade12ToGraduateTransition(array $assignments, AcademicYear $newYear): array
    {
        $results = [
            'graduated' => [],
            'repeated' => [],
            'failed' => [],
        ];

        DB::beginTransaction();
        try {
            foreach ($assignments as $assignment) {
                $student = Student::find($assignment['student_id']);

                if (!$student) {
                    $results['failed'][] = [
                        'student_id' => $assignment['student_id'],
                        'reason' => 'Student not found',
                    ];
                    continue;
                }

                if ($assignment['status'] === 'Lulus') {
                    // Graduate the student
                    $student->class_id = null;
                    $student->is_active = false;
                    $student->graduation_year = now()->year;
                    $student->save();

                    $results['graduated'][] = [
                        'student_id' => $student->id,
                        'student_name' => $student->name,
                    ];
                } else {
                    // Student repeats Grade 12
                    $targetClass = SchoolClass::find($assignment['class_id']);
                    
                    if (!$targetClass || $targetClass->grade_level != 12) {
                        $results['failed'][] = [
                            'student_id' => $student->id,
                            'student_name' => $student->name,
                            'reason' => 'Invalid repeat class',
                        ];
                        continue;
                    }

                    $student->class_id = $targetClass->id;
                    $student->save();

                    $results['repeated'][] = [
                        'student_id' => $student->id,
                        'student_name' => $student->name,
                        'class' => $targetClass->name,
                    ];
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Grade 12→Graduate transition failed: ' . $e->getMessage());
            throw $e;
        }

        return $results;
    }

    /**
     * Validate all students have been assigned before finalizing
     */
    public function validateAllStudentsAssigned(array $assignments, int $expectedCount): bool
    {
        return count($assignments) === $expectedCount;
    }
}