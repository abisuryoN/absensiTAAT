<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Core log writer.
     */
    public static function log(
        string $action,
        string $description,
        ?Model $model = null,
        ?array $properties = null,
        string $module = 'Sistem'
    ): ActivityLog {
        $user       = Auth::user();
        $causerRole = $user?->roles?->first()?->name ?? 'system';

        return ActivityLog::create([
            'user_id'    => Auth::id(),
            'causer_role'=> $causerRole,
            'action'     => $action,
            'module'     => $module,
            'model_type' => $model ? get_class($model) : null,
            'model_id'   => $model?->getKey(),
            'description'=> $description,
            'properties' => $properties,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    // ── CRUD helpers ─────────────────────────────────────────────────────────

    public static function logCreate(Model $model, ?string $description = null, string $module = 'Data'): ActivityLog
    {
        $label = class_basename($model);
        return self::log(
            'create',
            $description ?? "Menambahkan {$label} #{$model->getKey()}",
            $model,
            ['attributes' => $model->getAttributes()],
            $module
        );
    }

    public static function logUpdate(Model $model, array $original, ?string $description = null, string $module = 'Data'): ActivityLog
    {
        $changes = $model->getChanges();
        $label   = class_basename($model);
        return self::log(
            'update',
            $description ?? "Mengubah {$label} #{$model->getKey()}",
            $model,
            [
                'old' => array_intersect_key($original, $changes),
                'new' => $changes,
            ],
            $module
        );
    }

    public static function logDelete(Model $model, ?string $description = null, string $module = 'Data'): ActivityLog
    {
        $label = class_basename($model);
        return self::log(
            'delete',
            $description ?? "Menghapus {$label} #{$model->getKey()}",
            $model,
            ['attributes' => $model->getAttributes()],
            $module
        );
    }

    // ── Domain-specific helpers ───────────────────────────────────────────────

    public static function logImport(string $type, int $count, ?array $errors = null): ActivityLog
    {
        return self::log(
            'import',
            "Import {$type}: {$count} data berhasil" . ($errors ? ', ' . count($errors) . ' gagal' : ''),
            null,
            ['type' => $type, 'count' => $count, 'errors' => $errors],
            'Import/Export'
        );
    }

    public static function logExport(string $type, int $count, string $format = ''): ActivityLog
    {
        $fmt = $format ? " ({$format})" : '';
        return self::log(
            'export',
            "Mengunduh rekap {$type}{$fmt}: {$count} data",
            null,
            ['type' => $type, 'count' => $count, 'format' => $format],
            'Import/Export'
        );
    }

    public static function logAttendanceScan(string $studentName, string $status, ?string $note = null): ActivityLog
    {
        return self::log(
            'attendance_scan',
            "Scan absensi: {$studentName} — {$status}",
            null,
            ['student' => $studentName, 'status' => $status, 'note' => $note],
            'Absensi'
        );
    }

    public static function logAttendanceManual(string $studentName, string $status): ActivityLog
    {
        return self::log(
            'attendance_manual',
            "Input absensi manual: {$studentName} — {$status}",
            null,
            ['student' => $studentName, 'status' => $status],
            'Absensi'
        );
    }

    public static function logPasswordReset(string $targetName, string $targetRole): ActivityLog
    {
        return self::log(
            'password_reset',
            "Reset password akun: {$targetName} (Role: {$targetRole})",
            null,
            ['target_user' => $targetName, 'target_role' => $targetRole],
            'Manajemen Akun'
        );
    }

    public static function logNewSuperAdmin(string $targetName, string $targetEmail): ActivityLog
    {
        return self::log(
            'create_super_admin',
            "Menambah Super Admin baru: {$targetName} ({$targetEmail})",
            null,
            ['name' => $targetName, 'email' => $targetEmail],
            'Manajemen Super Admin'
        );
    }

    public static function logToggleSuperAdmin(string $targetName, bool $isActive): ActivityLog
    {
        $verb = $isActive ? 'mengaktifkan' : 'menonaktifkan';
        return self::log(
            'toggle_super_admin',
            "Super Admin {$verb}: {$targetName}",
            null,
            ['name' => $targetName, 'is_active' => $isActive],
            'Manajemen Super Admin'
        );
    }
}