<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Import extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'file_name',
        'file_path',
        'total_rows',
        'success_rows',
        'failed_rows',
        'errors',
        'preview_data',
        'status',
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'success_rows' => 'integer',
        'failed_rows' => 'integer',
        'errors' => 'array',
        'preview_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
