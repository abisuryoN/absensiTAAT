<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $teacherId = $this->route('teacher');
        $teacher = $teacherId ? \App\Models\Teacher::find($teacherId) : null;
        $userId = $teacher ? $teacher->user_id : null;

        return [
            'nip' => 'nullable|string|max:30|unique:teachers,nip,' . $teacherId,
            'nuptk' => 'nullable|string|max:30|unique:teachers,nuptk,' . $teacherId,
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:100|unique:users,email,' . $userId,
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|in:L,P',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'password' => $teacherId ? 'nullable|min:6' : 'nullable|min:6',
            'is_active' => 'nullable|boolean',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap guru wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Alamat email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'nip.unique' => 'NIP sudah terdaftar.',
            'nuptk.unique' => 'NUPTK sudah terdaftar.',
            'gender.required' => 'Jenis kelamin wajib diisi.',
            'photo.image' => 'File foto harus berupa gambar.',
            'photo.max' => 'Ukuran foto maksimal adalah 2MB.',
        ];
    }
}
