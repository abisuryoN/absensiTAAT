<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WhatsappTemplate;

class WhatsappTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Notifikasi Kehadiran Gerbang (Hadir)',
                'key' => 'attendance_gate_hadir',
                'body' => "Halo Bapak/Ibu.\n\nAnanda *{student_name}* (NIS: {nis}), kelas {class_name}, telah *HADIR* di sekolah.\n\nJam: {time_in} WIB\n\nTerima kasih.\n*SMAN 1 Tajurhalang*",
                'variables' => ['student_name', 'nis', 'class_name', 'time_in'],
                'is_active' => true,
            ],
            [
                'name' => 'Notifikasi Kehadiran Gerbang (Terlambat)',
                'key' => 'attendance_gate_terlambat',
                'body' => "Halo Bapak/Ibu.\n\nAnanda *{student_name}* (NIS: {nis}), kelas {class_name}, telah hadir *TERLAMBAT* di sekolah.\n\nJam: {time_in} WIB\nKeterangan: Terlambat {late_minutes} menit\n\nMohon bantuannya untuk memantau kedisiplinan Ananda.\n\nTerima kasih.\n*SMAN 1 Tajurhalang*",
                'variables' => ['student_name', 'nis', 'class_name', 'time_in', 'late_minutes'],
                'is_active' => true,
            ],
            [
                'name' => 'Notifikasi Kehadiran Gerbang (Alpha / Tidak Hadir)',
                'key' => 'attendance_gate_alpha',
                'body' => "Halo Bapak/Ibu.\n\nAnanda *{student_name}* (NIS: {nis}), kelas {class_name}, tercatat *TIDAK HADIR* (Alpha) tanpa keterangan pada hari ini.\n\nMohon segera menghubungi pihak sekolah jika terdapat halangan.\n\nTerima kasih.\n*SMAN 1 Tajurhalang*",
                'variables' => ['student_name', 'nis', 'class_name'],
                'is_active' => true,
            ],
            [
                'name' => 'Pengingat Absensi Mata Pelajaran Guru',
                'key' => 'teacher_reminder',
                'body' => "Yth. Bapak/Ibu Guru *{teacher_name}*,\n\nMohon diingatkan untuk mengisi absensi pelajaran *{subject_name}* di kelas *{class_name}* hari ini ({date}).\n\nSilakan login ke sistem untuk melakukan pengisian.\n\nTerima kasih atas kerjasamanya.\n*SMAN 1 Tajurhalang*",
                'variables' => ['teacher_name', 'subject_name', 'class_name', 'date'],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            WhatsappTemplate::updateOrCreate(['key' => $template['key']], $template);
        }
    }
}
