<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Log an activity.
     */
    public static function log(
        string $action,
        string $description,
        ?Model $model = null,
        ?array $properties = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->getKey(),
            'description' => $description,
            'properties' => $properties,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log a CRUD create action.
     */
    public static function logCreate(Model $model, ?string $description = null): ActivityLog
    {
        $label = class_basename($model);
        return self::log(
            'create',
            $description ?? "Menambahkan {$label} #{$model->getKey()}",
            $model,
            ['attributes' => $model->getAttributes()]
        );
    }

    /**
     * Log a CRUD update action.
     */
    public static function logUpdate(Model $model, array $original, ?string $description = null): ActivityLog
    {
        $label = class_basename($model);
        $changes = $model->getChanges();

        return self::log(
            'update',
            $description ?? "Mengubah {$label} #{$model->getKey()}",
            $model,
            [
                'old' => array_intersect_key($original, $changes),
                'new' => $changes,
            ]
        );
    }

    /**
     * Log a CRUD delete action.
     */
    public static function logDelete(Model $model, ?string $description = null): ActivityLog
    {
        $label = class_basename($model);
        return self::log(
            'delete',
            $description ?? "Menghapus {$label} #{$model->getKey()}",
            $model,
            ['attributes' => $model->getAttributes()]
        );
    }

    /**
     * Log a restore (soft delete undo).
     */
    public static function logRestore(Model $model, ?string $description = null): ActivityLog
    {
        $label = class_basename($model);
        return self::log(
            'restore',
            $description ?? "Memulihkan {$label} #{$model->getKey()}",
            $model
        );
    }

    /**
     * Log an import action.
     */
    public static function logImport(string $type, int $count, ?array $errors = null): ActivityLog
    {
        return self::log(
            'import',
            "Import {$type}: {$count} data berhasil" . ($errors ? ', ' . count($errors) . ' gagal' : ''),
            null,
            ['type' => $type, 'count' => $count, 'errors' => $errors]
        );
    }

    /**
     * Log an export action.
     */
    public static function logExport(string $type, int $count): ActivityLog
    {
        return self::log(
            'export',
            "Export {$type}: {$count} data",
            null,
            ['type' => $type, 'count' => $count]
        );
    }
}
