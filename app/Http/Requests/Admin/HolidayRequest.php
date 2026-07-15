<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class HolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => 'required|exists:academic_years,id',
            'name' => 'required|string|max:150',
            'date' => 'required|date',
            'type' => 'required|in:Nasional,Sekolah,Khusus',
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'name.required' => 'Nama hari libur wajib diisi.',
            'date.required' => 'Tanggal libur wajib diisi.',
            'type.required' => 'Tipe hari libur wajib dipilih.',
        ];
    }
}
