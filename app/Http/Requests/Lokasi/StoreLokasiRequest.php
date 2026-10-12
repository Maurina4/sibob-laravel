<?php

namespace App\Http\Requests\Lokasi;

use Illuminate\Foundation\Http\FormRequest;

class StoreLokasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255', 'unique:lokasis,nama'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama lokasi wajib diisi.',
            'nama.unique' => 'Nama lokasi tersebut sudah ada.',
        ];
    }
}
