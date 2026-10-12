<?php

namespace App\Http\Requests\Bmn;

use Illuminate\Foundation\Http\FormRequest;

class StoreBmnRequest extends FormRequest
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
            'manual_pengguna' => ['nullable', 'file', 'max:500', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx'],
            'panduan_perbaikan' => ['nullable', 'file', 'max:500', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx'],
            'instruksi_kerja' => ['nullable', 'file', 'max:500', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx'],
            'foto_barang' => ['nullable', 'image', 'max:500', 'mimes:jpg,jpeg,png,webp'],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis.required' => 'Jenis Barang wajib dipilih.',
            'jenis.in' => 'Jenis Barang harus BMN-TIK, BMN-LAB, atau BMN-NON TIK.',
            'manual_pengguna.max' => 'Ukuran Manual Pengguna maksimal 500 KB.',
            'panduan_perbaikan.max' => 'Ukuran Panduan Perbaikan maksimal 500 KB.',
            'instruksi_kerja.max' => 'Ukuran Instruksi Kerja maksimal 500 KB.',
            'foto_barang.max' => 'Ukuran Foto Barang maksimal 500 KB.',
            'manual_pengguna.mimes' => 'Manual Pengguna harus berformat PDF, DOC, DOCX, XLS, XLSX, PPT, atau PPTX.',
            'panduan_perbaikan.mimes' => 'Panduan Perbaikan harus berformat PDF, DOC, DOCX, XLS, XLSX, PPT, atau PPTX.',
            'instruksi_kerja.mimes' => 'Instruksi Kerja harus berformat PDF, DOC, DOCX, XLS, XLSX, PPT, atau PPTX.',
            'foto_barang.image' => 'Foto Barang harus berupa file gambar.',
            'foto_barang.mimes' => 'Foto Barang harus berformat JPG, JPEG, PNG, atau WEBP.',
        ];
    }
}
