<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\StudentParent;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Services\PasswordGeneratorService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class AccountManagementController extends Controller
{
    protected PasswordGeneratorService $passwordGenerator;

    public function __construct(PasswordGeneratorService $passwordGenerator)
    {
        $this->passwordGenerator = $passwordGenerator;
    }

    public function index(Request $request)
    {
        $role       = $request->get('role', 'all');
        $search     = $request->get('search', '');
        $classId    = $request->get('class_id');
        $gradeLevel = $request->get('grade_level');
        $majorId    = $request->get('major_id');

        $accounts = collect();

        // ── Siswa ──────────────────────────────────────────────
        if ($role === 'all' || $role === 'siswa') {
            $query = Student::with(['user', 'class.major'])->whereHas('user');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nisn', 'like', "%{$search}%")
                      ->orWhere('nis',  'like', "%{$search}%");
                });
            }
            if ($classId)    { $query->where('class_id', $classId); }
            if ($gradeLevel) { $query->whereHas('class', fn($q) => $q->where('grade_level', $gradeLevel)); }
            if ($majorId)    { $query->whereHas('class', fn($q) => $q->where('major_id', $majorId)); }

            foreach ($query->orderBy('name')->get() as $student) {
                $accounts->push([
                    'role'             => 'siswa',
                    'id'               => $student->id,
                    'name'             => $student->name,
                    'email'            => $student->user->email ?? '-',
                    'nisn'             => $student->nisn,
                    'nip'              => null,
                    'nik'              => null,
                    'class'            => $student->class?->name ?? '-',
                    'subjects'         => null,
                    'children'         => null,
                    'is_active'        => $student->is_active,
                    'password_changed' => false,
                ]);
            }
        }

        // ── Guru ───────────────────────────────────────────────
        if ($role === 'all' || $role === 'guru') {
            $query = Teacher::with(['user', 'subjects'])->whereHas('user');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nip',  'like', "%{$search}%");
                });
            }

            foreach ($query->orderBy('name')->get() as $teacher) {
                $subjects = $teacher->subjects->pluck('name')->join(', ');
                $accounts->push([
                    'role'             => 'guru',
                    'id'               => $teacher->id,
                    'name'             => $teacher->name,
                    'email'            => $teacher->user->email ?? '-',
                    'nisn'             => null,
                    'nip'              => $teacher->nip,
                    'nik'              => null,
                    'class'            => null,
                    'subjects'         => $subjects ?: '-',
                    'children'         => null,
                    'is_active'        => $teacher->is_active,
                    'password_changed' => false,
                ]);
            }
        }

        // ── Orang Tua ──────────────────────────────────────────
        if ($role === 'all' || $role === 'parent') {
            $query = StudentParent::with(['user', 'students.class'])->whereHas('user');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nik',  'like', "%{$search}%");
                });
            }

            foreach ($query->orderBy('name')->get() as $parent) {
                $childNames = $parent->students->pluck('name')->join(', ');
                $accounts->push([
                    'role'             => 'parent',
                    'id'               => $parent->id,
                    'name'             => $parent->name,
                    'email'            => $parent->user->email ?? '-',
                    'nisn'             => null,
                    'nip'              => null,
                    'nik'              => $parent->nik,
                    'class'            => null,
                    'subjects'         => null,
                    'children'         => $childNames ?: '-',
                    'is_active'        => $parent->is_active,
                    'password_changed' => false,
                ]);
            }
        }

        $classes    = SchoolClass::where('is_active', true)->orderBy('grade_level')->orderBy('name')->get();
        $majors     = Major::orderBy('name')->get();
        $totalCount = $accounts->count();

        // Manual pagination using LengthAwarePaginator
        $perPage   = 20;
        $page      = max(1, (int) $request->get('page', 1));
        $paginated = $accounts->forPage($page, $perPage)->values();

        $accounts = new LengthAwarePaginator(
            $paginated,
            $totalCount,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.accounts.index', compact(
            'accounts',
            'role', 'search', 'classId', 'gradeLevel', 'majorId',
            'classes', 'majors'
        ));
    }

    /**
     * Reset a user account's password to the role-default pattern.
     * Returns JSON: { success, name, new_password }
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'type' => 'required|in:siswa,guru,parent',
            'id'   => 'required|integer',
        ]);

        $type = $request->input('type');
        $id   = (int) $request->input('id');

        switch ($type) {
            case 'siswa':
                $model = Student::with('user')->findOrFail($id);
                $newPassword = $this->passwordGenerator->generateForStudent($model);
                $user = $model->user;
                $name = $model->name;
                break;

            case 'guru':
                $model = Teacher::with('user')->findOrFail($id);
                $newPassword = $this->passwordGenerator->generateForTeacher($model);
                $user = $model->user;
                $name = $model->name;
                break;

            case 'parent':
                $model = StudentParent::with(['user', 'students'])->findOrFail($id);
                $newPassword = $this->passwordGenerator->generateForParent($model);
                $user = $model->user;
                $name = $model->name;
                break;

            default:
                return response()->json(['success' => false, 'message' => 'Tipe tidak valid.'], 422);
        }

        if (!$user) {
            return redirect()->route('admin.accounts.index')
                ->with('error', 'Akun login tidak ditemukan untuk pengguna ini.');
        }

        $user->update(['password' => Hash::make($newPassword)]);

        ActivityLogService::logPasswordReset($name, $type);

        return redirect()->route('admin.accounts.index')
            ->with('reset_result', [
                'message'  => "Password untuk {$name} berhasil direset.",
                'password' => $newPassword,
            ]);
    }
}