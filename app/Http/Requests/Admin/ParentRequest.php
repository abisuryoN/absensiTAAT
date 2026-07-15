<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $parentId = $this->route('parent');
        $parent = $parentId ? \App\Models\StudentParent::find($parentId) : null;
        $userId = $parent ? $parent->user_id : null;

        return [
            'name' => 'required|string|max:150',
            'phone' => 'required|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'relationship' => 'required|in:Ayah,Ibu,Wali',
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:100|unique:users,email,' . $userId,
            'password' => 'nullable|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama orang tua wajib diisi.',
            'phone.required' => 'Nomor HP aktif wajib diisi.',
            'relationship.required' => 'Hubungan keluarga wajib dipilih.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
        ];
    }
}
