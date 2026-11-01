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
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'satuan' => ['required', 'string', 'max:100'],
            'lokasi_asal' => ['required', 'string', 'max:255'],
            'lokasi_tujuan' => ['required', 'string', 'max:255'],
            'penerima' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nomor.required' => 'Nomor Berita Acara wajib diisi.',
            'tanggal.required' => 'Tanggal Berita Acara wajib diisi.',
            'tanggal.before_or_equal' => 'Tanggal distribusi tidak boleh melebihi tanggal hari ini (tidak bisa memilih tanggal di masa depan).',
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'jumlah.required' => 'Jumlah barang wajib diisi.',
            'satuan.required' => 'Satuan barang wajib diisi.',
            'lokasi_asal.required' => 'Lokasi asal wajib diisi.',
            'lokasi_tujuan.required' => 'Lokasi tujuan penempatan wajib diisi.',
            'penerima.required' => 'Nama pegawai penerima wajib diisi.',
        ];
    }
}
