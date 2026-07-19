<?php

namespace App\Services;

use App\Models\StudentParent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ParentService
{
    public function getAll(array $filters = [])
    {
        $query = StudentParent::with(['user', 'students.class'])
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $q->where(function ($sub) use ($filters) {
                    $sub->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('nik', 'like', "%{$filters['search']}%")
                        ->orWhere('phone', 'like', "%{$filters['search']}%");
                });
            })
            ->orderBy('name');

        return $query->paginate(15)->withQueryString();
    }

    public function store(array $data): StudentParent
    {
        return DB::transaction(function () use ($data) {
            $userId = null;

            // Create login account if email is provided
            if (!empty($data['email'])) {
                if (empty($data['password'])) {
                    // Temporarily create the parent record to get NIK, then generate password
                    // Since we need students linked for tahun_masuk, use NIK only at creation time
                    $tempParent = new \App\Models\StudentParent(['nik' => $data['nik'] ?? null]);
                    $password = (new \App\Services\PasswordGeneratorService())->generateForParent($tempParent);
                } else {
                    $password = $data['password'];
                }

                $user = User::create([
                    'name'      => $data['name'],
                    'email'     => $data['email'],
                    'password'  => Hash::make($password),
                    'is_active' => $data['is_active'] ?? true,
                ]);
                $user->assignRole('parent');
                $userId = $user->id;
            }

            return StudentParent::create([
                'user_id'          => $userId,
                'name'             => $data['name'],
                'nik'              => $data['nik'],
                'phone'            => $data['phone'] ?? null,
                'phone_secondary'  => $data['phone_secondary'] ?? null,
                'relationship'     => $data['relationship'] ?? 'wali',
                'address'          => $data['address'] ?? null,
                'email'            => $data['email'] ?? null,
                'is_active'        => $data['is_active'] ?? true,
            ]);
        });
    }

    public function update(StudentParent $parent, array $data): StudentParent
    {
        return DB::transaction(function () use ($parent, $data) {
            $isActive = $data['is_active'] ?? false;

            // Handle login account
            if (!empty($data['email'])) {
                if ($parent->user) {
                    // Update existing account
                    $updateData = [
                        'name'      => $data['name'],
                        'email'     => $data['email'],
                        'is_active' => $isActive,
                    ];
                    if (!empty($data['password'])) {
                        $updateData['password'] = Hash::make($data['password']);
                    }
                    $parent->user->update($updateData);
                } else {
                    // Create new account
                    if (empty($data['password'])) {
                        $tempParent = new \App\Models\StudentParent(['nik' => $data['nik'] ?? null]);
                        $password = (new \App\Services\PasswordGeneratorService())->generateForParent($tempParent);
                    } else {
                        $password = $data['password'];
                    }
                    $user = User::create([
                        'name'      => $data['name'],
                        'email'     => $data['email'],
                        'password'  => Hash::make($password),
                        'is_active' => $isActive,
                    ]);
                    $user->assignRole('parent');
                    $data['user_id'] = $user->id;
                }
            } else {
                // Email removed — remove account link
                if ($parent->user) {
                    $parent->user->delete();
                }
                $data['user_id'] = null;
            }

            $parent->update([
                'user_id'         => $data['user_id'] ?? $parent->user_id,
                'name'            => $data['name'],
                'nik'             => $data['nik'],
                'phone'           => $data['phone'] ?? null,
                'phone_secondary' => $data['phone_secondary'] ?? null,
                'relationship'    => $data['relationship'] ?? 'wali',
                'address'         => $data['address'] ?? null,
                'email'           => $data['email'] ?? null,
                'is_active'       => $isActive,
            ]);

            return $parent->fresh();
        });
    }

    public function delete(StudentParent $parent): void
    {
        DB::transaction(function () use ($parent) {
            // Detach students (set parent_id to null)
            $parent->students()->update(['parent_id' => null]);

            // Delete linked user account if exists
            if ($parent->user) {
                $parent->user->delete();
            }

            $parent->delete();
        });
    }

    /**
     * Return paginated list for the picker modal (AJAX).
     */
    public function pickerSearch(string $search = '', int $perPage = 10)
    {
        $query = StudentParent::withCount('students')->where('is_active', true);

        if ($search) {
            $query->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name')->paginate($perPage);
    }
}