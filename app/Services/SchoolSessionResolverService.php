<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\GradeSessionSetting;
use App\Models\SchoolSession;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Support\Facades\Cache;

/**
 * SchoolSessionResolverService
 *
 * Resolves the correct SchoolSession for a student using the priority chain:
 *   1. Class-level override  (classes.school_session_id)
 *   2. Grade-level mapping   (grade_session_settings, active academic year)
 *   3. Default session       (settings.default_school_session_id)
 *   4. First active session  (fallback)
 *
 * All DB reads are cached to avoid N+1 hits on every scan.
 */
class SchoolSessionResolverService
{
    protected const CACHE_TTL = 300; // 5 minutes

    /**
     * Resolve the session for a specific student.
     */
    public function resolve(Student $student): ?SchoolSession
    {
        // 1. Class override
        if ($student->class_id) {
            $classSession = $this->getClassOverrideSession($student->class_id);
            if ($classSession) {
                return $classSession;
            }

            // 2. Grade-level mapping (filter by active academic year)
            $gradeLevel = optional($student->schoolClass)->grade_level;
            if ($gradeLevel) {
                $gradeSession = $this->getGradeMappingSession($gradeLevel);
                if ($gradeSession) {
                    return $gradeSession;
                }
            }
        }

        // 3. Default session
        return $this->getDefaultSession();
    }

    /**
     * Resolve session for a given grade level (without student).
     */
    public function resolveForGrade(int $gradeLevel): ?SchoolSession
    {
        return $this->getGradeMappingSession($gradeLevel) ?? $this->getDefaultSession();
    }

    // ── Internal resolvers ────────────────────────────────────────────────────

    protected function getClassOverrideSession(int $classId): ?SchoolSession
    {
        $overrides = Cache::remember('session.class_overrides', self::CACHE_TTL, function () {
            // Load all classes that have an explicit session override
            return \App\Models\SchoolClass::whereNotNull('school_session_id')
                ->with('schoolSession')
                ->get()
                ->keyBy('id')
                ->map(fn ($c) => $c->schoolSession)
                ->filter()
                ->toArray();
        });

        return isset($overrides[$classId])
            ? SchoolSession::find($overrides[$classId]['id'] ?? null)
            : null;
    }

    protected function getGradeMappingSession(int $gradeLevel): ?SchoolSession
    {
        $activeYearId = $this->getActiveAcademicYearId();
        if (!$activeYearId) {
            return null;
        }

        $mappings = Cache::remember("session.grade_mappings.{$activeYearId}", self::CACHE_TTL, function () use ($activeYearId) {
            return GradeSessionSetting::where('academic_year_id', $activeYearId)
                ->with('schoolSession')
                ->get()
                ->keyBy('grade_level')
                ->map(fn ($g) => $g->schoolSession)
                ->filter();
        });

        return $mappings->get($gradeLevel);
    }

    public function getDefaultSession(): ?SchoolSession
    {
        return Cache::remember('session.default', self::CACHE_TTL, function () {
            $defaultId = Setting::getVal('default_school_session_id');
            if ($defaultId) {
                $session = SchoolSession::where('id', $defaultId)->where('is_active', true)->first();
                if ($session) return $session;
            }
            // Fallback: first active session
            return SchoolSession::where('is_active', true)->first();
        });
    }

    protected function getActiveAcademicYearId(): ?int
    {
        return Cache::remember('academic_year.active_id', self::CACHE_TTL, function () {
            return AcademicYear::active()->first()?->id;
        });
    }

    // ── All sessions (for UI) ─────────────────────────────────────────────────

    public function getAllActive(): \Illuminate\Support\Collection
    {
        return Cache::remember('session.all_active', self::CACHE_TTL, function () {
            return SchoolSession::where('is_active', true)->orderBy('gate_open_time')->get();
        });
    }

    /**
     * Get grade→session mappings for the active academic year.
     */
    public function getGradeMappings(): \Illuminate\Support\Collection
    {
        $activeYearId = $this->getActiveAcademicYearId();
        if (!$activeYearId) {
            return collect();
        }

        return Cache::remember("session.grade_mappings.{$activeYearId}", self::CACHE_TTL, function () use ($activeYearId) {
            return GradeSessionSetting::where('academic_year_id', $activeYearId)
                ->with('schoolSession')
                ->get()
                ->keyBy('grade_level');
        });
    }

    // ── Cache invalidation ────────────────────────────────────────────────────

    public static function clearCache(): void
    {
        Cache::forget('session.class_overrides');
        Cache::forget('session.default');
        Cache::forget('session.all_active');
        Cache::forget('academic_year.active_id');

        // Clear grade mappings for all plausible academic year IDs
        $activeId = AcademicYear::active()->first()?->id;
        if ($activeId) {
            Cache::forget("session.grade_mappings.{$activeId}");
        }
    }
}
