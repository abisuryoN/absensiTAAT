<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;

class SchedulePolicy
{
    public function before(User $user): ?bool
    {
        if ($user->hasRole('super_admin')) return true;
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function view(User $user, Schedule $schedule): bool
    {
        if ($user->hasRole('guru')) {
            return $schedule->teacher?->user_id === $user->id;
        }
        if ($user->hasRole('siswa')) {
            $student = \App\Models\Student::where('user_id', $user->id)->first();
            return $student && (int) $schedule->class_id === (int) $student->class_id;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function update(User $user, Schedule $schedule): bool
    {
        return $user->hasRole('super_admin');
    }

    public function delete(User $user, Schedule $schedule): bool
    {
        return $user->hasRole('super_admin');
    }
}
