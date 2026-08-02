<?php

namespace Database\Seeders;

use App\Models\SchoolSession;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SchoolSessionSeeder extends Seeder
{
    public function run(): void
    {
        $sessions = [
            [
                'name'                  => 'Pagi',
                'gate_open_time'        => '06:00:00',
                'school_start_time'     => '07:00:00',
                'late_threshold_minutes'=> 15,
                'gate_close_time'       => '08:00:00',
                'auto_alpha_time'       => '09:00:00',
                'school_end_time'       => '13:00:00',
                'is_active'             => true,
            ],
            [
                'name'                  => 'Siang',
                'gate_open_time'        => '12:00:00',
                'school_start_time'     => '13:00:00',
                'late_threshold_minutes'=> 15,
                'gate_close_time'       => '14:00:00',
                'auto_alpha_time'       => '15:00:00',
                'school_end_time'       => '18:00:00',
                'is_active'             => true,
            ],
        ];

        foreach ($sessions as $data) {
            $session = SchoolSession::firstOrCreate(['name' => $data['name']], $data);

            // Set the first session as the default
            if ($data['name'] === 'Pagi') {
                Setting::where('key', 'default_school_session_id')->update(['value' => $session->id]);
            }
        }
    }
}
