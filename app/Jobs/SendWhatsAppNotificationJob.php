<?php

namespace App\Jobs;

use App\Models\AttendanceGate;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public AttendanceGate $attendance;

    /**
     * Create a new job instance.
     */
    public function __construct(AttendanceGate $attendance)
    {
        $this->attendance = $attendance;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsAppService): void
    {
        $student = $this->attendance->student;
        $parent = $student->parent;

        // Skip if student doesn't have parent mapped or parent doesn't have phone
        if (!$parent || empty($parent->phone)) {
            return;
        }

        $waktu = substr($this->attendance->time_in, 0, 5);
        $tanggal = $this->attendance->date->format('d-m-Y');
        $nama = $student->name;
        $status = strtoupper($this->attendance->status);
        $note = $this->attendance->note ?: '-';

        // Choose/construct template based on attendance status
        if ($this->attendance->status === 'hadir') {
            $message = "Halo Bapak/Ibu, menginformasikan bahwa putra/putri Anda yang bernama {$nama} telah tiba di sekolah pada tanggal {$tanggal} pukul {$waktu} WIB dengan status HADIR.";
        } elseif ($this->attendance->status === 'terlambat') {
            $message = "PEMBERITAHUAN: Putra/putri Anda yang bernama {$nama} tiba di sekolah terlambat pada tanggal {$tanggal} pukul {$waktu} WIB. Mohon bimbingannya.";
        } else {
            $message = "PEMBERITAHUAN: Putra/putri Anda yang bernama {$nama} hari ini tanggal {$tanggal} tercatat berstatus {$status} dengan keterangan: {$note}.";
        }

        $whatsAppService->send(
            $parent->phone,
            $message,
            $student->id,
            $parent->id,
            'attendance_gate'
        );
    }
}
