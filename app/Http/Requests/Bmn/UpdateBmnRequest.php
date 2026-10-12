<?php

namespace App\Http\Requests\Bmn;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBmnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_barang' => ['required', 'string', 'max:255'],
            'nup' => ['required', 'string', 'max:255'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'merek' => ['nullable', 'string', 'max:255'],
            'tanggal_terima' => ['nullable', 'date'],
            'jenis' => ['required', 'string', 'in:BMN-TIK,BMN-LAB,BMN-NON TIK'],
            'satuan' => ['nullable', 'string', 'max:255'],
            'lokasi' => ['required', 'string', 'max:255'],
            'penanggungjawab' => ['required', 'string', 'max:255'],
            'penanggungjawab_nip' => ['nullable', 'string', 'max:255'],
            'kondisi' => ['required', 'string', 'max:255'],
            'jadwal_pemeliharaan' => ['nullable', 'date'],
            'spesifikasi' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis.required' => 'Jenis Barang wajib dipilih.',
            'jenis.in' => 'Jenis Barang harus BMN-TIK, BMN-LAB, atau BMN-NON TIK.',
        ];
    }
}
