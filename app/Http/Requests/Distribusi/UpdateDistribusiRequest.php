<?php

namespace App\Http\Requests\Distribusi;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDistribusiRequest extends FormRequest
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
            'jumlah' => ['required', 'integer', 'min:1'],
            'satuan' => ['required', 'string', 'max:100'],
            'lokasi_asal' => ['required', 'string', 'max:255'],
            'lokasi_tujuan' => ['required', 'string', 'max:255'],
            'penerima' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:255'],
        ];
    }
}
