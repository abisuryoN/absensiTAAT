<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if ($setting) {
                // If it is a boolean, cast it
                if ($setting->type === 'boolean' || $setting->type === 'bool') {
                    $val = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                } else {
                    $val = $value;
                }
                Setting::setVal($key, $val, $setting->group);
            }
        }

        \App\Services\ActivityLogService::log(
            'update_settings',
            "Memperbarui pengaturan sistem",
            null,
            $data
        );

        return redirect()->route('admin.settings.index')->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }
}
