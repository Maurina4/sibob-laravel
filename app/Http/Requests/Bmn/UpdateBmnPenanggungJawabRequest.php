<?php

namespace App\Http\Requests\Bmn;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBmnPenanggungJawabRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'penanggungjawab' => ['required', 'string', 'max:255'],
            'penanggung_jawab_id' => ['nullable', 'integer', 'exists:pegawais,id'],
            'penanggungjawab_nip' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'penanggungjawab.required' => 'Penanggung jawab wajib dipilih.',
            'penanggung_jawab_id.exists' => 'Data pegawai penanggung jawab tidak valid.',
        ];
    }
}
