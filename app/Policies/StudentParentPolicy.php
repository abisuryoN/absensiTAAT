<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentParentPolicy
{
    /**
     * Determine whether the parent user can view this student's data.
     * The student must be linked to the parent account of the auth user.
     */
    public function viewDashboard(User $user, Student $student): bool
    {
        $parent = $user->parent;

        if (!$parent) {
            return false;
        }

        return (int) $student->parent_id === (int) $parent->id;
    }
}
