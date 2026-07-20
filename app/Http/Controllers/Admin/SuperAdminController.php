<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SuperAdminController extends Controller
{
    /**
     * List all super admin accounts.
     */
    public function index()
    {
        $superAdmins = User::role('super_admin')
            ->withTrashed()  // include soft-deleted to show full picture
            ->whereNull('deleted_at') // but only non-deleted
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.super-admins.index', compact('superAdmins'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('admin.super-admins.create');
    }

    /**
     * Store a new super admin account.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'              => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->numbers(),
            ],
        ], [
            'name.required'                 => 'Nama lengkap wajib diisi.',
            'email.required'                => 'Email wajib diisi.',
            'email.unique'                  => 'Email sudah digunakan oleh akun lain.',
            'password.required'             => 'Password wajib diisi.',
            'password.confirmed'            => 'Konfirmasi password tidak cocok.',
            'password.min'                  => 'Password minimal 8 karakter.',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'is_active' => true,
        ]);

        $user->assignRole('super_admin');

        ActivityLogService::logNewSuperAdmin($user->name, $user->email);

        return redirect()->route('admin.super-admins.index')
            ->with('success', "Super Admin '{$user->name}' berhasil ditambahkan.");
    }

    /**
     * Toggle active/inactive status of a super admin.
     * Cannot deactivate yourself or the last active super admin.
     */
    public function toggleActive(Request $request, User $superAdmin)
    {
        // Cannot modify yourself
        if ($superAdmin->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat mengubah status akun Anda sendiri melalui halaman ini.');
        }

        // Prevent deactivating if this is the last active super admin
        if ($superAdmin->is_active) {
            $activeCount = User::role('super_admin')
                ->where('is_active', true)
                ->count();

            if ($activeCount <= 1) {
                return back()->with('error', 'Tidak dapat menonaktifkan Super Admin ini karena merupakan satu-satunya Super Admin aktif di sistem.');
            }
        }

        $newStatus = !$superAdmin->is_active;
        $superAdmin->update(['is_active' => $newStatus]);

        ActivityLogService::logToggleSuperAdmin($superAdmin->name, $newStatus);

        $label = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun '{$superAdmin->name}' berhasil {$label}.");
    }
}