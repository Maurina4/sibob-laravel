<?php

namespace App\Http\Requests\Pengembalian;

use Illuminate\Foundation\Http\FormRequest;

class StorePengembalianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nomor_pengajuan' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'nama_pengaju' => ['required', 'string', 'max:255'],
            'bidang' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nomor_pengajuan.required' => 'Nomor pengajuan wajib diisi.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'nama_pengaju.required' => 'Nama pengaju wajib dipilih.',
            'bidang.required' => 'Bidang wajib dipilih.',
        ];
    }
}
