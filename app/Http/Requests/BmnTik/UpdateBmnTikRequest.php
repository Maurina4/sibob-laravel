<?php

namespace App\Http\Requests\BmnTik;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBmnTikRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_barang' => ['required', 'string', 'max:100'],
            'nup' => ['required', 'integer', 'min:1'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'jenis' => ['nullable', 'string', 'max:100'],
            'lokasi' => ['required', 'string', 'max:255'],
            'penanggungjawab' => ['required', 'string', 'max:255'],
        ];
    }
}
