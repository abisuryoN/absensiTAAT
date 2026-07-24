<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'is_active',
        'profile_photo',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Accessor: URL foto profil atau fallback ke avatar inisial.
     * Gunakan $user->profile_photo_url di seluruh Blade.
     */
    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo && Storage::disk('public')->exists($this->profile_photo)) {
            return Storage::url($this->profile_photo);
        }

        // Fallback: UI Avatars API dengan inisial nama, warna berdasarkan role
        $name = urlencode($this->name ?? 'U');
        $bgColors = ['4f46e5', '0ea5e9', '10b981', 'f59e0b', 'ef4444', '8b5cf6'];
        $colorIndex = crc32($this->email ?? $this->name ?? 'u') % count($bgColors);
        $bg = $bgColors[abs($colorIndex)];

        return "https://ui-avatars.com/api/?name={$name}&background={$bg}&color=fff&size=128&bold=true";
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function parent(): HasOne
    {
        return $this->hasOne(StudentParent::class, 'user_id');
    }
}
