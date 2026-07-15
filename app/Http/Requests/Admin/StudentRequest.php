<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->route('student');
        $student = $studentId ? \App\Models\Student::find($studentId) : null;
        $userId = $student ? $student->user_id : null;

        return [
            'nis' => 'required|string|max:30|unique:students,nis,' . $studentId,
            'nisn' => 'nullable|string|max:30|unique:students,nisn,' . $studentId,
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:100|unique:users,email,' . $userId,
            'gender' => 'required|in:L,P',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'class_id' => 'required|exists:classes,id',
            'parent_id' => 'nullable|exists:parents,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'password' => $studentId ? 'nullable|min:6' : 'nullable|min:6',
            'barcode_id' => 'nullable|string|max:50|unique:students,barcode_id,' . $studentId,
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah terdaftar.',
            'nisn.unique' => 'NISN sudah terdaftar.',
            'name.required' => 'Nama lengkap siswa wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Alamat email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'class_id.required' => 'Kelas wajib dipilih.',
            'class_id.exists' => 'Kelas tidak valid.',
            'parent_id.exists' => 'Orang tua tidak valid.',
            'photo.image' => 'File foto harus berupa gambar.',
            'photo.max' => 'Ukuran foto maksimal adalah 2MB.',
        ];
    }
}
