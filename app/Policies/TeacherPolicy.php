<?php

namespace App\Policies;

use App\Models\Teacher;
use App\Models\User;

class TeacherPolicy
{
    /**
     * Super admin can do everything.
     */
    public function before(User $user): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any teachers (admin index).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can view the teacher's own data.
     */
    public function view(User $user, Teacher $teacher): bool
    {
        return (int) $teacher->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can create teachers.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can update the teacher.
     */
    public function update(User $user, Teacher $teacher): bool
    {
        return (int) $teacher->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can delete the teacher.
     */
    public function delete(User $user, Teacher $teacher): bool
    {
        return $user->hasRole('super_admin');
    }
}
