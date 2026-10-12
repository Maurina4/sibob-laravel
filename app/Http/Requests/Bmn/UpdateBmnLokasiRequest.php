<?php

namespace App\Http\Requests\Bmn;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBmnLokasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lokasi' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'lokasi.required' => 'Lokasi wajib dipilih.',
        ];
    }
}
