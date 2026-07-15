<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Attendance config
            [
                'group' => 'attendance',
                'key' => 'gate_open_time',
                'value' => '05:30',
                'type' => 'string',
                'description' => 'Jam gerbang sekolah dibuka untuk absensi masuk (HH:MM)'
            ],
            [
                'group' => 'attendance',
                'key' => 'school_start_time',
                'value' => '06:30',
                'type' => 'string',
                'description' => 'Jam masuk resmi sekolah (HH:MM)'
            ],
            [
                'group' => 'attendance',
                'key' => 'late_threshold_minutes',
                'value' => '15',
                'type' => 'integer',
                'description' => 'Toleransi keterlambatan dalam menit'
            ],
            [
                'group' => 'attendance',
                'key' => 'gate_close_time',
                'value' => '08:00',
                'type' => 'string',
                'description' => 'Jam gerbang sekolah ditutup untuk absensi (HH:MM)'
            ],
            [
                'group' => 'attendance',
                'key' => 'school_end_time',
                'value' => '15:30',
                'type' => 'string',
                'description' => 'Jam kepulangan sekolah (HH:MM)'
            ],
            [
                'group' => 'attendance',
                'key' => 'auto_alpha_time',
                'value' => '23:00',
                'type' => 'string',
                'description' => 'Jam otomatis menandai siswa Alpha jika belum scan (HH:MM)'
            ],
            [
                'group' => 'attendance',
                'key' => 'teacher_fill_deadline_hours',
                'value' => '24',
                'type' => 'integer',
                'description' => 'Batas waktu guru mengisi absensi pelajaran (jam)'
            ],
            
            // QR Token config
            [
                'group' => 'qr_token',
                'key' => 'qr_token_ttl_seconds',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Masa berlaku token QR Code siswa (detik)'
            ],
            
            // WhatsApp config
            [
                'group' => 'whatsapp',
                'key' => 'whatsapp_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Aktifkan pengiriman notifikasi WhatsApp'
            ],
            [
                'group' => 'whatsapp',
                'key' => 'whatsapp_provider',
                'value' => 'fonnte',
                'type' => 'string',
                'description' => 'Provider WhatsApp Gateway (fonnte, wablas, woowa)'
            ],
            [
                'group' => 'whatsapp',
                'key' => 'whatsapp_api_url',
                'value' => 'https://api.fonnte.com/send',
                'type' => 'string',
                'description' => 'Endpoint API WhatsApp Gateway'
            ],
            [
                'group' => 'whatsapp',
                'key' => 'whatsapp_api_token',
                'value' => '',
                'type' => 'string',
                'description' => 'Token API / API Key WhatsApp Gateway'
            ],
            [
                'group' => 'whatsapp',
                'key' => 'whatsapp_sender_device',
                'value' => '',
                'type' => 'string',
                'description' => 'ID Perangkat pengirim WhatsApp (jika diperlukan)'
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
