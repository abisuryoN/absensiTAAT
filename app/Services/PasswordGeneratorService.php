<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\StudentParent;

/**
 * Generates default passwords for each role based on their unique identifiers.
 *
 * Rules:
 *  - Siswa  : NISN + tahun_masuk          (e.g. "12345678902024")
 *  - Guru   : NIP  + tahun_masuk_kerja    (e.g. "1985010120100110012015")
 *  - Parent : NIK  + tahun_masuk_anak     (earliest child's tahun_masuk, e.g. "3201xxxx2024")
 *
 * Fallback values are used when the primary fields are empty so the service
 * always returns a usable password string (never null / empty).
 */
class PasswordGeneratorService
{
    /**
     * Generate default password for a student.
     * Pattern: NISN + tahun_masuk
     * Fallback: NIS + tahun_masuk, or NIS alone if tahun_masuk is null.
     */
    public function generateForStudent(Student $student): string
    {
        $identifier = $student->nisn ?? $student->nis;
        $year       = $student->tahun_masuk;

        if ($identifier && $year) {
            return $identifier . $year;
        }

        // Partial fallback
        if ($identifier) {
            return $identifier;
        }

        return 'siswa123';
    }

    /**
     * Generate default password for a teacher.
     * Pattern: NIP + tahun_masuk_kerja
     * Fallback: NIP alone, or 'guru123'.
     */
    public function generateForTeacher(Teacher $teacher): string
    {
        $nip  = $teacher->nip;
        $year = $teacher->tahun_masuk_kerja;

        if ($nip && $year) {
            return $nip . $year;
        }

        if ($nip) {
            return $nip;
        }

        return 'guru123';
    }

    /**
     * Generate default password for a parent/wali.
     * Pattern: NIK + tahun_masuk of the earliest enrolled child.
     * If no children are linked yet, the raw NIK is used.
     * Fallback: NIK alone, or 'ortu123'.
     *
     * @param StudentParent $parent  Must have 'students' relationship loaded (or will lazy-load).
     */
    public function generateForParent(StudentParent $parent): string
    {
        $nik = $parent->nik;

        // Find the earliest tahun_masuk among linked students
        $year = null;
        $students = $parent->students ?? $parent->students()->orderBy('tahun_masuk')->get();
        foreach ($students as $student) {
            if ($student->tahun_masuk) {
                if ($year === null || $student->tahun_masuk < $year) {
                    $year = $student->tahun_masuk;
                }
            }
        }

        if ($nik && $year) {
            return $nik . $year;
        }

        if ($nik) {
            return $nik;
        }

        return 'ortu123';
    }

    /**
     * Generate a default password given a role name and the related model.
     * Convenience method for use inside the AccountManagementController.
     *
     * @param  string  $role   'siswa' | 'guru' | 'parent'
     * @param  mixed   $model  Student|Teacher|StudentParent
     */
    public function generateForRole(string $role, $model): string
    {
        return match ($role) {
            'siswa'  => $this->generateForStudent($model),
            'guru'   => $this->generateForTeacher($model),
            'parent' => $this->generateForParent($model),
            default  => 'password123',
        };
    }
}