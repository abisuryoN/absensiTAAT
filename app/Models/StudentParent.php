<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentParent extends Model
{
    use HasFactory, SoftDeletes;

    // Use absolute table name because Model class name is StudentParent
    protected $table = 'parents';

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'phone_secondary',
        'relationship',
        'address',
    ];
    /**
     * Set the relationship attribute to lowercase.
     */
    public function setRelationshipAttribute($value)
    {
        $this->attributes['relationship'] = strtolower($value);
    }

    /**
     * Get the relationship attribute capitalized.
     */
    public function getRelationshipAttribute($value)
    {
        return ucfirst(strtolower($value));
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'parent_id');
    }
}
