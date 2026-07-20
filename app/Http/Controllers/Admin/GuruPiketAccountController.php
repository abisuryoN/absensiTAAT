<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class GuruPiketAccountController extends Controller
{
    /**
     * List all guru piket accounts.
     */
    public function index()
    {
        $accounts = User::role('guru_piket')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.guru-piket-accounts.index', compact('accounts'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('admin.guru-piket-accounts.create');
    }

    /**
     * Store a new guru piket account.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(6),
            ],
        ], [
            'name.required'      => 'Nama akun wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.unique'       => 'Email sudah digunakan oleh akun lain.',
            'password.required'  => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'       => 'Password minimal 6 karakter.',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'is_active' => true,
        ]);

        $user->assignRole('guru_piket');

        ActivityLogService::log(
            'create',
            "Akun Guru Piket '{$user->name}' ({$user->email}) berhasil dibuat oleh " . Auth::user()->name,
            $user,
            null,
            'Manajemen Akun'
        );

        return redirect()->route('admin.guru-piket-accounts.index')
            ->with('success', "Akun Guru Piket '{$user->name}' berhasil dibuat.");
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleActive(Request $request, User $guruPiketAccount)
    {
        $newStatus = !$guruPiketAccount->is_active;
        $guruPiketAccount->update(['is_active' => $newStatus]);

        ActivityLogService::log(
            'update',
            "Status akun Guru Piket '{$guruPiketAccount->name}' " . ($newStatus ? 'diaktifkan' : 'dinonaktifkan'),
            $guruPiketAccount,
            null,
            'Manajemen Akun'
        );

        $label = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun '{$guruPiketAccount->name}' berhasil {$label}.");
    }

    /**
     * Delete a guru piket account.
     */
    public function destroy(User $guruPiketAccount)
    {
        $name = $guruPiketAccount->name;
        $guruPiketAccount->delete();

        ActivityLogService::log(
            'delete',
            "Akun Guru Piket '{$name}' dihapus oleh " . Auth::user()->name,
            null,
            null,
            'Manajemen Akun'
        );

        return redirect()->route('admin.guru-piket-accounts.index')
            ->with('success', "Akun '{$name}' berhasil dihapus.");
    }
}