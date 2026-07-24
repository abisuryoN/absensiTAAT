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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportService
{
    /**
     * Parse spreadsheet file, validate template structure, and validate all data rows.
     */
    public function previewAndValidate(string $filePath, string $type): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        // 1. Validate template (structure, headers, version)
        $this->validateTemplate($worksheet, $type);

        $rows = $worksheet->toArray();
        $headers = array_map(function($h) {
            return strtolower(trim((string)$h));
        }, $rows[0]);

        $dataRows = array_slice($rows, 1);
        $errors = [];
        $validData = [];

        // Pre-fetch DB references for high performance and avoiding N+1 queries
        $cache = $this->prepareCacheForValidation($type);

        // Track duplicates inside the uploaded file
        $fileDuplicates = [
            'email' => [],
            'nis' => [],
            'nisn' => [],
            'nip' => [],
            'nuptk' => [],
            'class_name' => [],
        ];

        foreach ($dataRows as $index => $row) {
            $rowNum = $index + 2;

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Skip notes rows (e.g. CATATAN: or PASSWORD: rows)
            $firstCell = trim((string)($row[0] ?? ''));
            if (stripos($firstCell, 'CATATAN:') !== false || stripos($firstCell, 'PASSWORD:') !== false) {
                continue;
            }

            // Map row data using header names
            $mappedRow = [];
            foreach ($headers as $headerIdx => $headerName) {
                if (!empty($headerName)) {
                    $mappedRow[$headerName] = $row[$headerIdx] ?? null;
                }
            }

            // Validate data in row
            $rowErrors = $this->validateRowData($mappedRow, $type, $rowNum, $cache, $fileDuplicates);

            if (!empty($rowErrors)) {
                $errors = array_merge($errors, $rowErrors);
            } else {
                $validData[] = $mappedRow;
            }
        }

        return [
            'errors' => $errors,
            'valid_data' => $validData,
            'total_rows' => count($dataRows),
            'valid_rows_count' => count($validData),
            'invalid_rows_count' => count(array_unique(array_column($errors, 'row'))),
        ];
    }

    /**
     * Validate spreadsheet headers, worksheet title, column counts and template version.
     */
    protected function validateTemplate(Worksheet $worksheet, string $type): void
    {
        $expectedSheetTitles = [
            'students' => 'Template Import Siswa',
            'teachers' => 'Template Import Guru',
            'classes' => 'Template Import Kelas',
            'schedules' => 'Template Import Jadwal',
        ];

        $expectedHeaders = [
            'students' => ['nis', 'nisn', 'name', 'email', 'gender', 'phone', 'class_name', 'parent_id', 'template_version'],
            'teachers' => ['nip', 'nuptk', 'name', 'email', 'gender', 'phone', 'template_version'],
            'classes' => ['academic_year', 'major_code', 'grade_level', 'name', 'capacity', 'template_version'],
            'schedules' => ['teacher_email', 'subject_code', 'class_name', 'day', 'start_time', 'end_time', 'room', 'template_version'],
        ];

        // 1. Validate Sheet Name
        $sheetTitle = $worksheet->getTitle();
        if ($sheetTitle !== $expectedSheetTitles[$type]) {
            throw new \Exception("Format file tidak sesuai dengan template. Nama worksheet '{$sheetTitle}' tidak sesuai. Silakan gunakan template resmi.");
        }

        $rows = $worksheet->toArray();
        if (empty($rows) || count($rows) < 1) {
            throw new \Exception('File spreadsheet kosong atau tidak memiliki baris data.');
        }

        // 2. Normalize and check headers
        $headers = array_map(function($h) {
            return $h === null ? '' : strtolower(trim((string)$h));
        }, $rows[0]);

        $expected = $expectedHeaders[$type];

        // Validate column order and names
        foreach ($expected as $i => $expectedHeader) {
            if (!isset($headers[$i]) || $headers[$i] === '') {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
                throw new \Exception("Format file tidak sesuai dengan template. Silakan download template terbaru dan isi kembali sesuai format.\n\nKolom ke-" . ($i + 1) . " ({$colLetter})\nKolom " . str_replace('_', ' ', $expectedHeader) . " tidak ditemukan.");
            }
            if ($headers[$i] !== $expectedHeader) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
                $foundHeader = $rows[0][$i] ?? 'Kosong';
                throw new \Exception("Format file tidak sesuai dengan template. Silakan download template terbaru dan isi kembali sesuai format.\n\nKolom ke-" . ($i + 1) . " ({$colLetter})\nDitemukan: {$foundHeader}\nSeharusnya: {$expectedHeader}");
            }
        }

        // Validate for extra columns
        if (count($headers) > count($expected)) {
            $extraColIndex = count($expected);
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($extraColIndex + 1);
            $extraHeader = $rows[0][$extraColIndex] ?? 'Kosong';
            throw new \Exception("Format file tidak sesuai dengan template. Silakan download template terbaru dan isi kembali sesuai format.\n\nKolom ke-" . ($extraColIndex + 1) . " ({$colLetter})\nDitemukan kolom tambahan: {$extraHeader}");
        }

        // 3. Validate Version (v1.0)
        if (count($rows) > 1) {
            $versionIndex = array_search('template_version', $expected);
            if ($versionIndex !== false) {
                $versionVal = trim((string)($rows[1][$versionIndex] ?? ''));
                if ($versionVal !== 'v1.0') {
                    throw new \Exception("Template yang digunakan sudah tidak sesuai. Silakan download template terbaru.");
                }
            }
        }
    }

    /**
     * Pre-fetch reference data from DB to avoid N+1 query overhead.
     */
    protected function prepareCacheForValidation(string $type): array
    {
        $cache = [];
        if ($type === 'students') {
            $cache['nis'] = Student::pluck('nis')->map(fn($v) => (string)$v)->toArray();
            $cache['nisn'] = Student::whereNotNull('nisn')->pluck('nisn')->map(fn($v) => (string)$v)->toArray();
            $cache['emails'] = User::pluck('email')->map(fn($v) => strtolower($v))->toArray();
            $cache['classes'] = SchoolClass::pluck('id', 'name')->toArray();
            $cache['parents'] = StudentParent::pluck('id')->toArray();
        } elseif ($type === 'teachers') {
            $cache['nips'] = Teacher::whereNotNull('nip')->pluck('nip')->map(fn($v) => (string)$v)->toArray();
            $cache['nuptks'] = Teacher::whereNotNull('nuptk')->pluck('nuptk')->map(fn($v) => (string)$v)->toArray();
            $cache['emails'] = User::pluck('email')->map(fn($v) => strtolower($v))->toArray();
        } elseif ($type === 'classes') {
            $cache['academic_years'] = AcademicYear::pluck('id', 'name')->toArray();
            $cache['majors'] = Major::pluck('id', 'code')->toArray();
            $cache['classes'] = SchoolClass::select('name', 'academic_year_id')->get()
                ->groupBy('academic_year_id')
                ->map(fn($g) => $g->pluck('name')->toArray())
                ->toArray();
        } elseif ($type === 'schedules') {
            $cache['teachers'] = User::role('guru')->pluck('email')->map(fn($v) => strtolower($v))->toArray();
            $cache['subjects'] = Subject::pluck('id', 'code')->toArray();
            $cache['classes'] = SchoolClass::pluck('id', 'name')->toArray();
        }
        return $cache;
    }

    /**
     * Validate data rules for a single row.
     */
    protected function validateRowData(array $row, string $type, int $rowNum, array $cache, array &$fileDuplicates): array
    {
        $errors = [];

        switch ($type) {
            case 'students':
                // Required validations
                $requiredFields = ['nis' => 'NIS', 'name' => 'Nama', 'email' => 'Email', 'gender' => 'Jenis Kelamin', 'class_name' => 'Kelas'];
                foreach ($requiredFields as $field => $label) {
                    if (empty(trim((string)($row[$field] ?? '')))) {
                        $errors[] = [
                            'row' => $rowNum,
                            'column' => $label,
                            'value' => '',
                            'message' => "Kolom {$label} tidak boleh kosong."
                        ];
                    }
                }

                // Email validation
                $email = strtolower(trim((string)($row['email'] ?? '')));
                if (!empty($email)) {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = [
                            'row' => $rowNum,
                            'column' => 'Email',
                            'value' => $row['email'],
                            'message' => 'Format email tidak valid.'
                        ];
                    } else {
                        // DB Unique
                        if (in_array($email, $cache['emails'])) {
                            $errors[] = [
                                'row' => $rowNum,
                                'column' => 'Email',
                                'value' => $row['email'],
                                'message' => 'Email sudah digunakan.'
                            ];
                        }
                        // File Unique
                        if (in_array($email, $fileDuplicates['email'])) {
                            $errors[] = [
                                'row' => $rowNum,
                                'column' => 'Email',
                                'value' => $row['email'],
                                'message' => 'Email duplikat di dalam file Excel.'
                            ];
                        } else {
                            $fileDuplicates['email'][] = $email;
                        }
                    }
                }

                // NIS validation
                $nis = trim((string)($row['nis'] ?? ''));
                if (!empty($nis)) {
                    if (!is_numeric($nis)) {
                        $errors[] = [
                            'row' => $rowNum,
                            'column' => 'NIS',
                            'value' => $row['nis'],
                            'message' => 'NIS harus berupa angka/string numerik.'
                        ];
                    } else {
                        // DB Unique
                        if (in_array($nis, $cache['nis'])) {
                            $errors[] = [
                                'row' => $rowNum,
                                'column' => 'NIS',
                                'value' => $row['nis'],
                                'message' => 'NIS sudah digunakan.'
                            ];
                        }
                        // File Unique
                        if (in_array($nis, $fileDuplicates['nis'])) {
                            $errors[] = [
                                'row' => $rowNum,
                                'column' => 'NIS',
                                'value' => $row['nis'],
                                'message' => 'NIS duplikat di dalam file Excel.'
                            ];
                        } else {
                            $fileDuplicates['nis'][] = $nis;
                        }
                    }
                }

                // NISN validation
                $nisn = trim((string)($row['nisn'] ?? ''));
                if (!empty($nisn)) {
                    if (!is_numeric($nisn)) {
                        $errors[] = [
                            'row' => $rowNum,
                            'column' => 'NISN',
                            'value' => $row['nisn'],
                            'message' => 'NISN harus berupa angka/string numerik.'
                        ];
                    } else {
                        // DB Unique
                        if (in_array($nisn, $cache['nisn'])) {
                            $errors[] = [
                                'row' => $rowNum,
                                'column' => 'NISN',
                                'value' => $row['nisn'],
                                'message' => 'NISN sudah digunakan.'
                            ];
                        }
                        // File Unique
                        if (in_array($nisn, $fileDuplicates['nisn'])) {
                            $errors[] = [
                                'row' => $rowNum,
                                'column' => 'NISN',
                                'value' => $row['nisn'],
                                'message' => 'NISN duplikat di dalam file Excel.'
                            ];
                        } else {
                            $fileDuplicates['nisn'][] = $nisn;
                        }
                    }
                }

                // Gender validation
                $gender = strtoupper(trim((string)($row['gender'] ?? '')));
                if (!empty($gender) && !in_array($gender, ['L', 'P'])) {
                    $errors[] = [
                        'row' => $rowNum,
                        'column' => 'Jenis Kelamin',
                        'value' => $row['gender'],
                        'message' => "Jenis Kelamin hanya boleh 'L' atau 'P'."
                    ];
                }

                // Phone validation
                $phone = trim((string)($row['phone'] ?? ''));
                if (!empty($phone) && !preg_match('/^[0-9+\-\s]+$/', $phone)) {
                    $errors[] = [
                        'row' => $rowNum,
                        'column' => 'No. HP',
                        'value' => $row['phone'],
                        'message' => 'Format nomor HP tidak valid.'
                    ];
                }

                // Class name validation
                $className = trim((string)($row['class_name'] ?? ''));
                if (!empty($className) && !isset($cache['classes'][$className])) {
                    $errors[] = [
                        'row' => $rowNum,
                        'column' => 'Kelas',
                        'value' => $row['class_name'],
                        'message' => "Kelas '{$className}' tidak ditemukan."
                    ];
                }

                // Parent ID validation
                $parentId = trim((string)($row['parent_id'] ?? ''));
                if (!empty($parentId) && !in_array($parentId, $cache['parents'])) {
                    $errors[] = [
                        'row' => $rowNum,
                        'column' => 'Parent ID',
                        'value' => $row['parent_id'],
                        'message' => "Parent ID '{$parentId}' tidak ditemukan di data master orang tua."
                    ];
                }
                break;

            case 'teachers':
                // Required validations
                $requiredFields = ['name' => 'Nama', 'email' => 'Email', 'gender' => 'Jenis Kelamin'];
                foreach ($requiredFields as $field => $label) {
                    if (empty(trim((string)($row[$field] ?? '')))) {
                        $errors[] = [
                            'row' => $rowNum,
                            'column' => $label,
                            'value' => '',
                            'message' => "Kolom {$label} tidak boleh kosong."
                        ];
                    }
                }

                // Email validation
                $email = strtolower(trim((string)($row['email'] ?? '')));
                if (!empty($email)) {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = [
                            'row' => $rowNum,
                            'column' => 'Email',
                            'value' => $row['email'],
                            'message' => 'Format email tidak valid.'
                        ];
                    } else {
                        // DB Unique
                        if (in_array($email, $cache['emails'])) {
                            $errors[] = [
                                'row' => $rowNum,
                                'column' => 'Email',
                                'value' => $row['email'],
                                'message' => 'Email sudah digunakan.'
                            ];
                        }
                        // File Unique
                        if (in_array($email, $fileDuplicates['email'])) {
                            $errors[] = [
                                'row' => $rowNum,
                                'column' => 'Email',
                                'value' => $row['email'],
                                'message' => 'Email duplikat di dalam file Excel.'
                            ];
                        } else {
                            $fileDuplicates['email'][] = $email;
                        }
                    }
                }

                // NIP validation
                $nip = trim((string)($row['nip'] ?? ''));
                if (!empty($nip)) {
                    if (!is_numeric($nip)) {
                        $errors[] = [
                            'row' => $rowNum,
                            'column' => 'NIP',
                            'value' => $row['nip'],
                            'message' => 'NIP harus berupa angka/string numerik.'
                        ];
                    } else {
                        // DB Unique
                        if (in_array($nip, $cache['nips'])) {
                            $errors[] = [
                                'row' => $rowNum,
                                'column' => 'NIP',
                                'value' => $row['nip'],
                                'message' => 'NIP sudah digunakan.'
                            ];
                        }
                        // File Unique
                        if (in_array($nip, $fileDuplicates['nip'])) {
                            $errors[] = [
                                'row' => $rowNum,
                                'column' => 'NIP',
                                'value' => $row['nip'],
                                'message' => 'NIP duplikat di dalam file Excel.'
                            ];
                        } else {
                            $fileDuplicates['nip'][] = $nip;
                        }
                    }
                }

                // NUPTK validation
                $nuptk = trim((string)($row['nuptk'] ?? ''));
                if (!empty($nuptk)) {
                    if (!is_numeric($nuptk)) {
                        $errors[] = [
                            'row' => $rowNum,
                            'column' => 'NUPTK',
                            'value' => $row['nuptk'],
                            'message' => 'NUPTK harus berupa angka/string numerik.'
                        ];
                    } else {
                        // DB Unique
                        if (in_array($nuptk, $cache['nuptks'])) {
                            $errors[] = [
                                'row' => $rowNum,
                                'column' => 'NUPTK',
                                'value' => $row['nuptk'],
                                'message' => 'NUPTK sudah digunakan.'
                            ];
                        }
                        // File Unique
                        if (in_array($nuptk, $fileDuplicates['nuptk'])) {
                            $errors[] = [
                                'row' => $rowNum,
                                'column' => 'NUPTK',
                                'value' => $row['nuptk'],
                                'message' => 'NUPTK duplikat di dalam file Excel.'
                            ];
                        } else {
                            $fileDuplicates['nuptk'][] = $nuptk;
                        }
                    }
                }

                // Gender validation
                $gender = strtoupper(trim((string)($row['gender'] ?? '')));
                if (!empty($gender) && !in_array($gender, ['L', 'P'])) {
                    $errors[] = [
                        'row' => $rowNum,
                        'column' => 'Jenis Kelamin',
                        'value' => $row['gender'],
                        'message' => "Jenis Kelamin hanya boleh 'L' atau 'P'."
                    ];
                }

                // Phone validation
                $phone = trim((string)($row['phone'] ?? ''));
                if (!empty($phone) && !preg_match('/^[0-9+\-\s]+$/', $phone)) {
                    $errors[] = [
                        'row' => $rowNum,
                        'column' => 'No. HP',
                        'value' => $row['phone'],
                        'message' => 'Format nomor HP tidak valid.'
                    ];
                }
                break;

            case 'classes':
                // Required validations
                $requiredFields = ['academic_year' => 'Tahun Ajaran', 'major_code' => 'Jurusan', 'grade_level' => 'Tingkat Kelas', 'name' => 'Nama Kelas', 'capacity' => 'Kapasitas'];
                foreach ($requiredFields as $field => $label) {
                    if (empty(trim((string)($row[$field] ?? '')))) {
                        $errors[] = [
                            'row' => $rowNum,
                            'column' => $label,
                            'value' => '',
                            'message' => "Kolom {$label} tidak boleh kosong."
                        ];
                    }
                }

                // Academic year validation
                $ay = trim((string)($row['academic_year'] ?? ''));
                $ayId = null;
                if (!empty($ay)) {
                    if (!isset($cache['academic_years'][$ay])) {
                        $errors[] = [
                            'row' => $rowNum,
                            'column' => 'Tahun Ajaran',
                            'value' => $row['academic_year'],
                            'message' => "Tahun Ajaran '{$ay}' tidak ditemukan."
                        ];
                    } else {
                        $ayId = $cache['academic_years'][$ay];
                    }
                }

                // Major code validation
                $major = trim((string)($row['major_code'] ?? ''));
                if (!empty($major) && !isset($cache['majors'][$major])) {
                    $errors[] = [
                        'row' => $rowNum,
                        'column' => 'Jurusan',
                        'value' => $row['major_code'],
                        'message' => "Jurusan dengan kode '{$major}' tidak ditemukan."
                    ];
                }

                // Grade level validation
                $gl = trim((string)($row['grade_level'] ?? ''));
                if (!empty($gl) && !in_array($gl, ['10', '11', '12'])) {
                    $errors[] = [
                        'row' => $rowNum,
                        'column' => 'Tingkat Kelas',
                        'value' => $row['grade_level'],
                        'message' => "Tingkat Kelas hanya boleh 10, 11, atau 12."
                    ];
                }

                // Capacity validation
                $cap = trim((string)($row['capacity'] ?? ''));
                if (!empty($cap)) {
                    if (!is_numeric($cap) || (int)$cap < 1 || (int)$cap > 100) {
                        $errors[] = [
                            'row' => $rowNum,
                            'column' => 'Kapasitas',
                            'value' => $row['capacity'],
                            'message' => 'Kapasitas harus berupa angka bulat antara 1 s.d. 100.'
                        ];
                    }
                }

                // Class Name validation
                $className = trim((string)($row['name'] ?? ''));
                if (!empty($className) && $ayId) {
                    // DB unique
                    if (isset($cache['classes'][$ayId]) && in_array($className, $cache['classes'][$ayId])) {
                        $errors[] = [
                            'row' => $rowNum,
                            'column' => 'Nama Kelas',
                            'value' => $row['name'],
                            'message' => "Kelas '{$className}' sudah terdaftar untuk Tahun Ajaran ini."
                        ];
                    }
                    // File unique
                    $fileKey = "{$ayId}-{$className}";
                    if (in_array($fileKey, $fileDuplicates['class_name'])) {
                        $errors[] = [
                            'row' => $rowNum,
                            'column' => 'Nama Kelas',
                            'value' => $row['name'],
                            'message' => "Kelas '{$className}' duplikat di dalam file Excel untuk Tahun Ajaran ini."
                        ];
                    } else {
                        $fileDuplicates['class_name'][] = $fileKey;
                    }
                }
                break;

            case 'schedules':
                // Required validations
                $requiredFields = [
                    'teacher_email' => 'Email Guru',
                    'subject_code' => 'Kode Mapel',
                    'class_name' => 'Nama Kelas',
                    'day' => 'Hari',
                    'start_time' => 'Jam Mulai',
                    'end_time' => 'Jam Selesai',
                ];
                foreach ($requiredFields as $field => $label) {
                    if (empty(trim((string)($row[$field] ?? '')))) {
                        $errors[] = [
                            'row' => $rowNum,
                            'column' => $label,
                            'value' => '',
                            'message' => "Kolom {$label} tidak boleh kosong."
                        ];
                    }
                }

                // Teacher email validation
                $tEmail = strtolower(trim((string)($row['teacher_email'] ?? '')));
                if (!empty($tEmail)) {
                    if (!filter_var($tEmail, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = [
                            'row' => $rowNum,
                            'column' => 'Email Guru',
                            'value' => $row['teacher_email'],
                            'message' => 'Format email tidak valid.'
                        ];
                    } elseif (!in_array($tEmail, $cache['teachers'])) {
                        $errors[] = [
                            'row' => $rowNum,
                            'column' => 'Email Guru',
                            'value' => $row['teacher_email'],
                            'message' => 'Email Guru tidak ditemukan atau tidak memiliki role Guru.'
                        ];
                    }
                }

                // Subject code validation
                $sCode = trim((string)($row['subject_code'] ?? ''));
                if (!empty($sCode) && !isset($cache['subjects'][$sCode])) {
                    $errors[] = [
                        'row' => $rowNum,
                        'column' => 'Kode Mapel',
                        'value' => $row['subject_code'],
                        'message' => "Mata Pelajaran dengan kode '{$sCode}' tidak ditemukan."
                    ];
                }

                // Class name validation
                $cName = trim((string)($row['class_name'] ?? ''));
                if (!empty($cName) && !isset($cache['classes'][$cName])) {
                    $errors[] = [
                        'row' => $rowNum,
                        'column' => 'Nama Kelas',
                        'value' => $row['class_name'],
                        'message' => "Kelas '{$cName}' tidak ditemukan."
                    ];
                }

                // Day validation
                $day = trim((string)($row['day'] ?? ''));
                $validDays = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                if (!empty($day) && !in_array($day, $validDays)) {
                    $errors[] = [
                        'row' => $rowNum,
                        'column' => 'Hari',
                        'value' => $row['day'],
                        'message' => "Hari tidak valid. Harus salah satu dari: " . implode(', ', $validDays)
                    ];
                }

                // Time format validations
                $startTime = trim((string)($row['start_time'] ?? ''));
                $endTime = trim((string)($row['end_time'] ?? ''));
                if (!empty($startTime) && !preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $startTime)) {
                    $errors[] = [
                        'row' => $rowNum,
                        'column' => 'Jam Mulai',
                        'value' => $row['start_time'],
                        'message' => 'Jam Mulai tidak valid. Format harus HH:MM (contoh: 07:00).'
                    ];
                }
                if (!empty($endTime) && !preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $endTime)) {
                    $errors[] = [
                        'row' => $rowNum,
                        'column' => 'Jam Selesai',
                        'value' => $row['end_time'],
                        'message' => 'Jam Selesai tidak valid. Format harus HH:MM (contoh: 08:30).'
                    ];
                }
                break;
        }

        return $errors;
    }

    /**
     * Import validated rows inside a database transaction.
     */
    public function import(array $rows, string $type): int
    {
        return DB::transaction(function () use ($rows, $type) {
            $successCount = 0;

            foreach ($rows as $row) {
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
                        if (!empty($row['parent_id'])) {
                            $parent = StudentParent::find($row['parent_id']);
                            if ($parent) {
                                $parentId = $parent->id;
                            }
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

            return $successCount;
        });
    }
}
