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

        // Whitelist: only allow keys that already exist in the settings table.
        // This prevents an authenticated admin from injecting arbitrary keys.
        $allowedKeys = Setting::pluck('key')->all();

        foreach ($data as $key => $value) {
            // Skip any key not in the whitelist
            if (!in_array($key, $allowedKeys, true)) {
                continue;
            }

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
