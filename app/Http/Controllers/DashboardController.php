<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user->roles->first()?->name;

        return match ($role) {
            'super_admin' => redirect()->route('admin.dashboard'),
            'guru'        => redirect()->route('teacher.dashboard'),
            'siswa'       => redirect()->route('student.dashboard'),
            'parent'      => redirect()->route('parent.dashboard'),
            default       => view('dashboard'),
        };
    }
}