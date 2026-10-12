<?php

namespace App\Http\Requests\BmnLab;

use Illuminate\Foundation\Http\FormRequest;

class StoreBmnLabRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_barang' => ['required', 'string', 'max:255'],
            'nup' => ['required', 'integer', 'min:1'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'merek' => ['nullable', 'string', 'max:255'],
            'lokasi' => ['required', 'string', 'max:255'],
            'penanggungjawab' => ['required', 'string', 'max:255'],
            'kondisi' => ['nullable', 'string', 'in:Baik,Rusak,Perlu Kalibrasi'],
            'status_kalibrasi' => ['nullable', 'string', 'in:Belum Dikalibrasi,Berlaku,Jatuh Tempo'],
            'tanggal_kalibrasi' => ['nullable', 'date'],
        ];
    }
}
