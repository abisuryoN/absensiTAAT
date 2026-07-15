<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'student_id',
        'parent_id',
        'type',
        'channel',
        'phone_number',
        'message',
        'status',
        'response',
        'sent_at',
        'attempts',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'attempts' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(StudentParent::class, 'parent_id');
    }
}
