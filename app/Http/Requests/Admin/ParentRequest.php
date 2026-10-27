<?php

namespace App\Http\Requests\Admin;

use App\Models\StudentParent;
use Illuminate\Foundation\Http\FormRequest;

class ParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Resolve the parent record being edited.
        $parent = $this->route('parent');

        if ($parent && !($parent instanceof StudentParent)) {
            $parent = StudentParent::find($parent);
        }

        // Get the linked user ID for the email uniqueness rule.
        $userId = $parent?->user_id;

        return [
            'name'            => 'required|string|max:150',
            'nik'             => 'required|string|max:20|unique:parents,nik,' . ($parent?->id ?? 'NULL'),
            'phone'           => 'nullable|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'relationship'    => 'nullable|in:ayah,ibu,wali',
            'address'         => 'nullable|string',
            'email'           => 'nullable|email|max:100|unique:users,email,' . ($userId ?? 'NULL'),
            'password'        => 'nullable|string|min:6',
            'is_active'       => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Nama lengkap wajib diisi.',
            'nik.required'   => 'NIK wajib diisi.',
            'nik.unique'     => 'NIK ini sudah terdaftar.',
            'email.unique'   => 'Email ini sudah digunakan oleh akun lain.',
            'email.email'    => 'Format email tidak valid.',
            'password.min'   => 'Password minimal harus 6 karakter.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
