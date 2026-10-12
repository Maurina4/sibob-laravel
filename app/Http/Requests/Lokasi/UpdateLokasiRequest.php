<?php

namespace App\Http\Requests\Lokasi;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLokasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $lokasiId = $this->route('lokasi') ? $this->route('lokasi')->id : null;

        return [
            'nama' => ['required', 'string', 'max:255', 'unique:lokasis,nama,' . $lokasiId],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama lokasi wajib diisi.',
            'nama.unique' => 'Nama lokasi tersebut sudah digunakan.',
        ];
    }
}
