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
 * Cache stores only IDs (integers), never Eloquent models, to prevent
 * __PHP_Incomplete_Class deserialization errors after schema changes.
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
        // Cache: map of class_id => school_session_id (integers only, no models)
        $overrides = Cache::remember('session.class_overrides', self::CACHE_TTL, function () {
            return \App\Models\SchoolClass::whereNotNull('school_session_id')
                ->pluck('school_session_id', 'id')
                ->toArray(); // [class_id => session_id]
        });

        $sessionId = $overrides[$classId] ?? null;

        return $sessionId
            ? SchoolSession::where('id', $sessionId)->where('is_active', true)->first()
            : null;
    }

    protected function getGradeMappingSession(int $gradeLevel): ?SchoolSession
    {
        $activeYearId = $this->getActiveAcademicYearId();
        if (! $activeYearId) {
            return null;
        }

        // Cache: map of grade_level => school_session_id (integers only, no models)
        $mappings = Cache::remember("session.grade_mappings.{$activeYearId}", self::CACHE_TTL, function () use ($activeYearId) {
            return GradeSessionSetting::where('academic_year_id', $activeYearId)
                ->whereNotNull('school_session_id')
                ->pluck('school_session_id', 'grade_level')
                ->toArray(); // [grade_level => session_id]
        });

        $sessionId = $mappings[$gradeLevel] ?? null;

        return $sessionId
            ? SchoolSession::where('id', $sessionId)->where('is_active', true)->first()
            : null;
    }

    public function getDefaultSession(): ?SchoolSession
    {
        // Cache: only the session ID (integer), never the full model
        $sessionId = Cache::remember('session.default_id', self::CACHE_TTL, function () {
            $defaultId = Setting::getVal('default_school_session_id');
            if ($defaultId) {
                $id = SchoolSession::where('id', $defaultId)
                    ->where('is_active', true)
                    ->value('id');
                if ($id) return $id;
            }
            // Fallback: first active session's ID
            return SchoolSession::where('is_active', true)->value('id');
        });

        return $sessionId ? SchoolSession::find($sessionId) : null;
    }

    protected function getActiveAcademicYearId(): ?int
    {
        // Already caches an integer — safe from deserialization issues
        return Cache::remember('academic_year.active_id', self::CACHE_TTL, function () {
            return AcademicYear::active()->first()?->id;
        });
    }

    // ── All sessions (for UI) ─────────────────────────────────────────────────

    public function getAllActive(): \Illuminate\Support\Collection
    {
        // Cache only IDs, fetch fresh models from DB
        $ids = Cache::remember('session.all_active_ids', self::CACHE_TTL, function () {
            return SchoolSession::where('is_active', true)
                ->orderBy('gate_open_time')
                ->pluck('id')
                ->toArray();
        });

        if (empty($ids)) {
            return collect();
        }

        return SchoolSession::whereIn('id', $ids)->orderBy('gate_open_time')->get();
    }

    /**
     * Get grade→session mappings for the active academic year.
     */
    public function getGradeMappings(): \Illuminate\Support\Collection
    {
        $activeYearId = $this->getActiveAcademicYearId();
        if (! $activeYearId) {
            return collect();
        }

        return GradeSessionSetting::where('academic_year_id', $activeYearId)
            ->with('schoolSession')
            ->get()
            ->keyBy('grade_level');
    }

    // ── Cache invalidation ────────────────────────────────────────────────────

    public static function clearCache(): void
    {
        Cache::forget('session.class_overrides');
        Cache::forget('session.default_id');
        Cache::forget('session.all_active_ids');
        Cache::forget('academic_year.active_id');

        // Clear grade mappings for the active academic year
        $activeId = AcademicYear::active()->first()?->id;
        if ($activeId) {
            Cache::forget("session.grade_mappings.{$activeId}");
        }
    }
}
