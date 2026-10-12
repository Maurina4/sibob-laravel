<?php

namespace App\Http\Requests\LaporanKerusakan;

use Illuminate\Foundation\Http\FormRequest;

class StoreLaporanKerusakanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nomor' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'lokasi' => ['required', 'string', 'max:255'],
            'tingkat' => ['required', 'in:Ringan,Sedang,Berat'],
            'deskripsi' => ['required', 'string'],
        ];
    }
}
