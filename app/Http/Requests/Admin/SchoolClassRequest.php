<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => 'required|exists:academic_years,id',
            'major_id' => 'required|exists:majors,id',
            'grade_level' => 'required|integer|min:10|max:12',
            'name' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1|max:100',
            'homeroom_teacher_id' => 'nullable|exists:teachers,id',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists' => 'Tahun ajaran tidak valid.',
            'major_id.required' => 'Jurusan wajib dipilih.',
            'major_id.exists' => 'Jurusan tidak valid.',
            'grade_level.required' => 'Tingkat kelas wajib diisi.',
            'name.required' => 'Nama kelas wajib diisi.',
            'capacity.required' => 'Kapasitas kelas wajib diisi.',
            'homeroom_teacher_id.exists' => 'Guru wali kelas tidak valid.',
        ];
    }
}
