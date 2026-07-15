<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Redirect users to their specific dashboard based on role.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('super_admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('guru')) {
            return redirect()->route('teacher.dashboard');
        } elseif ($user->hasRole('siswa')) {
            return redirect()->route('student.dashboard');
        }

        // Fallback or unauthorized user has no role
        Auth::logout();
        return redirect()->route('login')->withErrors([
            'email' => 'Akun Anda tidak memiliki peran (role) yang valid untuk mengakses sistem.',
        ]);
    }
}
