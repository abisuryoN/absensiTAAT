<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Major;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\User;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\Schedule;
use App\Models\ClassStudentHistory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportService
{
    /**
     * Parse spreadsheet file and validate row-by-row.
     */
    public function preview(string $filePath, string $type): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        if (count($rows) <= 1) {
            throw new \Exception('File spreadsheet kosong atau tidak memiliki baris data.');
        }

        $headers = array_map(function($h) {
            return strtolower(trim($h));
        }, $rows[0]);

        $dataRows = array_slice($rows, 1);
        $previewData = [];

        foreach ($dataRows as $index => $row) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Map row values to associative array using headers
            $mappedRow = [];
            foreach ($headers as $headerIdx => $headerName) {
                if (!empty($headerName)) {
                    $mappedRow[$headerName] = $row[$headerIdx] ?? null;
                }
            }

            // Validate row
            $validation = $this->validateRow($mappedRow, $type, $index + 2);

            $previewData[] = [
                'row_number' => $index + 2,
                'data' => $mappedRow,
                'is_valid' => $validation['is_valid'],
                'errors' => $validation['errors'],
            ];
        }

        return $previewData;
    }

    /**
     * Validate row based on type.
     */
    protected function validateRow(array $row, string $type, int $rowNum): array
    {
        $rules = [];
        $messages = [];

        switch ($type) {
            case 'students':
                $rules = [
                    'nis' => 'required|string|max:30|unique:students,nis',
                    'nisn' => 'nullable|string|max:30|unique:students,nisn',
                    'name' => 'required|string|max:150',
                    'email' => 'required|email|max:100|unique:users,email',
                    'gender' => 'required|in:L,P',
                    'phone' => 'nullable|string|max:20',
                    'class_name' => 'required|string',
                    'parent_name' => 'nullable|string|max:150',
                    'parent_phone' => 'nullable|string|max:20',
                ];
                break;

            case 'teachers':
                $rules = [
                    'nip' => 'nullable|string|max:30|unique:teachers,nip',
                    'nuptk' => 'nullable|string|max:30|unique:teachers,nuptk',
                    'name' => 'required|string|max:150',
                    'email' => 'required|email|max:100|unique:users,email',
                    'gender' => 'required|in:L,P',
                    'phone' => 'nullable|string|max:20',
                ];
                break;

            case 'classes':
                $rules = [
                    'academic_year' => 'required|string',
                    'major_code' => 'required|string',
                    'grade_level' => 'required|integer|in:10,11,12',
                    'name' => 'required|string|max:50',
                    'capacity' => 'required|integer|min:1|max:100',
                ];
                break;

            case 'schedules':
                $rules = [
                    'teacher_email' => 'required|email|exists:users,email',
                    'subject_code' => 'required|string|exists:subjects,code',
                    'class_name' => 'required|string',
                    'day' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
                    'start_time' => 'required|date_format:H:i',
                    'end_time' => 'required|date_format:H:i',
                    'room' => 'nullable|string|max:50',
                ];
                break;
        }

        $validator = Validator::make($row, $rules, $messages);
        $errors = $validator->errors()->all();

        // Additional relational checks
        if ($validator->passes()) {
            if ($type === 'students') {
                $class = SchoolClass::where('name', $row['class_name'])->first();
                if (!$class) {
                    $errors[] = "Kelas '{$row['class_name']}' tidak terdaftar.";
                }
            } elseif ($type === 'classes') {
                $year = AcademicYear::where('name', $row['academic_year'])->first();
                if (!$year) {
                    $errors[] = "Tahun Ajaran '{$row['academic_year']}' tidak terdaftar.";
                }
                $major = Major::where('code', $row['major_code'])->first();
                if (!$major) {
                    $errors[] = "Jurusan dengan kode '{$row['major_code']}' tidak terdaftar.";
                }
            } elseif ($type === 'schedules') {
                $class = SchoolClass::where('name', $row['class_name'])->first();
                if (!$class) {
                    $errors[] = "Kelas '{$row['class_name']}' tidak terdaftar.";
                }
            }
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Import validated rows.
     */
    public function import(array $rows, string $type): int
    {
        return DB::transaction(function () use ($rows, $type) {
            $successCount = 0;

            foreach ($rows as $row) {
                // Double check validity (only import valid ones)
                $validation = $this->validateRow($row, $type, 0);
                if (!$validation['is_valid']) {
                    continue;
                }

                switch ($type) {
                    case 'students':
                        $class = SchoolClass::where('name', $row['class_name'])->first();
                        
                        $user = User::create([
                            'name' => $row['name'],
                            'email' => $row['email'],
                            'password' => Hash::make($row['nis']),
                            'is_active' => true,
                        ]);
                        $user->assignRole('siswa');

                        $parentId = null;
                        if (!empty($row['parent_name']) && !empty($row['parent_phone'])) {
                            $parent = StudentParent::firstOrCreate([
                                'phone' => $row['parent_phone']
                            ], [
                                'name' => $row['parent_name'],
                                'relationship' => 'Orang Tua',
                            ]);
                            $parentId = $parent->id;
                        }

                        $student = Student::create([
                            'user_id' => $user->id,
                            'parent_id' => $parentId,
                            'class_id' => $class->id,
                            'nis' => $row['nis'],
                            'nisn' => $row['nisn'] ?? null,
                            'name' => $row['name'],
                            'gender' => $row['gender'],
                            'phone' => $row['phone'] ?? null,
                            'barcode_id' => $row['nis'],
                            'is_active' => true,
                        ]);

                        ClassStudentHistory::create([
                            'student_id' => $student->id,
                            'class_id' => $student->class_id,
                            'academic_year_id' => AcademicYear::active()->first()?->id ?? 1,
                        ]);
                        break;

                    case 'teachers':
                        $user = User::create([
                            'name' => $row['name'],
                            'email' => $row['email'],
                            'password' => Hash::make($row['nip'] ?? 'password123'),
                            'is_active' => true,
                        ]);
                        $user->assignRole('guru');

                        Teacher::create([
                            'user_id' => $user->id,
                            'nip' => $row['nip'] ?? null,
                            'nuptk' => $row['nuptk'] ?? null,
                            'name' => $row['name'],
                            'gender' => $row['gender'],
                            'phone' => $row['phone'] ?? null,
                            'is_active' => true,
                        ]);
                        break;

                    case 'classes':
                        $year = AcademicYear::where('name', $row['academic_year'])->first();
                        $major = Major::where('code', $row['major_code'])->first();

                        SchoolClass::create([
                            'academic_year_id' => $year->id,
                            'major_id' => $major->id,
                            'grade_level' => $row['grade_level'],
                            'name' => $row['name'],
                            'capacity' => $row['capacity'],
                            'is_active' => true,
                        ]);
                        break;

                    case 'schedules':
                        $class = SchoolClass::where('name', $row['class_name'])->first();
                        $teacherUser = User::where('email', $row['teacher_email'])->first();
                        $teacher = Teacher::where('user_id', $teacherUser->id)->first();
                        $subject = Subject::where('code', $row['subject_code'])->first();

                        Schedule::create([
                            'academic_year_id' => AcademicYear::active()->first()?->id ?? 1,
                            'semester_id' => \App\Models\Semester::active()->first()?->id ?? 1,
                            'teacher_id' => $teacher->id,
                            'subject_id' => $subject->id,
                            'class_id' => $class->id,
                            'day' => $row['day'],
                            'start_time' => $row['start_time'],
                            'end_time' => $row['end_time'],
                            'room' => $row['room'] ?? null,
                            'is_active' => true,
                        ]);
                        break;
                }

                $successCount++;
            }

            ActivityLogService::logImport($type, $successCount);

            return $successCount;
        });
    }
}
