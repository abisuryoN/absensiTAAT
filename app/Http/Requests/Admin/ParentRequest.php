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
        // Resolve the parent record being edited (if any)
        $parentId = $this->route('parent');
        $parent   = $parentId ? \App\Models\StudentParent::find($parentId) : null;

        // Get the user_id linked to this parent so we can exclude it from the email unique check
        $userId = $parent?->user_id;

        return [
            'name'            => 'required|string|max:150',
            'nik'             => 'required|string|max:20|unique:parents,nik,' . ($parent?->id ?? 'NULL'),
            'phone'           => 'nullable|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'relationship'    => 'nullable|in:ayah,ibu,wali',
            'address'         => 'nullable|string',
            'email'           => 'nullable|email|max:100|unique:users,email,' . ($userId ?? 'NULL'),
            'password'        => 'nullable|min:6',
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
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}