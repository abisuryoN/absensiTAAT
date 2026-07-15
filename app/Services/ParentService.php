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
        $query = StudentParent::with('user');

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('phone', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('name')->paginate(15);
    }

    public function store(array $data): StudentParent
    {
        return DB::transaction(function () use ($data) {
            $userId = null;

            // Optional User account for parent if email is provided
            if (!empty($data['email'])) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password'] ?? 'parent123'),
                    'is_active' => true,
                ]);
                $user->assignRole('siswa'); // Or parent role if defined, but we defined roles super_admin, guru, siswa
                $userId = $user->id;
            }

            $parent = StudentParent::create([
                'user_id' => $userId,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'phone_secondary' => $data['phone_secondary'] ?? null,
                'relationship' => $data['relationship'],
                'address' => $data['address'] ?? null,
            ]);

            ActivityLogService::logCreate($parent, "Menambahkan Orang Tua: {$parent->name}");

            return $parent;
        });
    }

    public function update(StudentParent $parent, array $data): StudentParent
    {
        return DB::transaction(function () use ($parent, $data) {
            $original = $parent->getAttributes();
            $user = $parent->user;

            if (!empty($data['email'])) {
                if ($user) {
                    $userData = [
                        'name' => $data['name'],
                        'email' => $data['email'],
                    ];
                    if (!empty($data['password'])) {
                        $userData['password'] = Hash::make($data['password']);
                    }
                    $user->update($userData);
                } else {
                    $user = User::create([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'password' => Hash::make($data['password'] ?? 'parent123'),
                        'is_active' => true,
                    ]);
                    $user->assignRole('siswa');
                    $parent->user_id = $user->id;
                }
            }

            $parent->update(array_intersect_key($data, array_flip([
                'name', 'phone', 'phone_secondary', 'relationship', 'address', 'user_id'
            ])));

            ActivityLogService::logUpdate($parent, $original, "Mengubah Orang Tua: {$parent->name}");

            return $parent;
        });
    }

    public function delete(StudentParent $parent): void
    {
        DB::transaction(function () use ($parent) {
            if ($parent->students()->count() > 0) {
                throw new \Exception('Orang tua tidak dapat dihapus karena memiliki data siswa terkait.');
            }

            $user = $parent->user;

            ActivityLogService::logDelete($parent, "Menghapus Orang Tua: {$parent->name}");
            $parent->delete();

            if ($user) {
                $user->delete();
            }
        });
    }
}
