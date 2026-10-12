<?php

namespace App\Http\Requests\BaPerpindahan;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBaPerpindahanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nomor_ba' => ['nullable', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'dari' => ['required', 'string', 'max:255'],
            'ke' => ['nullable', 'string', 'max:255'],
            'alasan' => ['required', 'string'],
            'pihak_kedua' => ['nullable', 'string', 'max:255'],
        ];
    }
}
