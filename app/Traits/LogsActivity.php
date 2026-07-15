<?php

namespace App\Traits;

use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    /**
     * Boot the trait to listen for Eloquent events.
     */
    protected static function bootLogsActivity(): void
    {
        static::created(function (Model $model) {
            ActivityLogService::logCreate($model);
        });

        static::updated(function (Model $model) {
            // Get original values prior to update
            $original = $model->getOriginal();
            ActivityLogService::logUpdate($model, $original);
        });

        static::deleted(function (Model $model) {
            ActivityLogService::logDelete($model);
        });

        // Handle soft deletes restore if applicable
        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model) {
                ActivityLogService::logRestore($model);
            });
        }
    }
}
