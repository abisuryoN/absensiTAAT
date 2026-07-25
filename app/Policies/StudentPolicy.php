<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function before(User $user): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function view(User $user, Student $student): bool
    {
        // Admin roles can see all students
        if ($user->hasRole('super_admin')) return true;

        // Teacher can view their own students via schedules
        if ($user->hasRole('guru')) return true;

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function update(User $user, Student $student): bool
    {
        return $user->hasRole('super_admin');
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->hasRole('super_admin');
    }
}
