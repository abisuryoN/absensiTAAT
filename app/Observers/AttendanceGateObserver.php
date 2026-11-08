<?php

namespace App\Observers;

use App\Models\AttendanceGate;
use App\Notifications\AttendanceAlert;

class AttendanceGateObserver
{
    /**
     * Handle the AttendanceGate "updated" event.
     */
    public function updated(AttendanceGate $attendanceGate): void
    {
        // Only send notification if status changed to terlambat or tidak_hadir
        if ($attendanceGate->wasChanged('status')) {
            $newStatus = $attendanceGate->status;
            
            if (in_array($newStatus, ['terlambat', 'tidak_hadir'])) {
                $student = $attendanceGate->student;
                $parent = $student?->parent;
                $parentUser = $parent?->user;

                if ($parentUser) {
                    $statusLabel = $newStatus === 'tidak_hadir' ? 'Tidak Hadir (Alpha)' : 'Terlambat';
                    $title = 'Pemberitahuan Kehadiran';
                    $message = "Anak Anda, {$student->name}, berstatus {$statusLabel} pada tanggal " 
                        . $attendanceGate->date->format('d/m/Y') . '.';
                    
                    // Check for duplicate notification to prevent spam
                    $alreadySent = $parentUser->notifications()
                        ->where('type', AttendanceAlert::class)
                        ->where('data->message', $message)
                        ->whereDate('created_at', now()->toDateString())
                        ->exists();

                    if (!$alreadySent) {
                        $parentUser->notify(new AttendanceAlert($title, $message));
                    }
                }
            }
        }
    }
}
