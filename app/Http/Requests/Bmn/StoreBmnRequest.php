<?php

namespace App\Http\Requests\Bmn;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBmnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];
        if ($this->has('kode_barang') && is_string($this->kode_barang)) {
            $merge['kode_barang'] = str_replace(['.', '-', ' '], '', trim($this->kode_barang));
        }
        if ($this->has('nup') && is_string($this->nup)) {
            $merge['nup'] = trim($this->nup);
        }
        if ($this->filled('nilai_perolehan') && is_string($this->nilai_perolehan)) {
            $cleaned = preg_replace('/[^0-9.]/', '', str_replace(',', '.', $this->nilai_perolehan));
            $merge['nilai_perolehan'] = $cleaned !== '' ? (float) $cleaned : null;
        }
        if (! empty($merge)) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'kode_barang' => [
                'required',
                'string',
                'digits:10',
                Rule::unique('bmns', 'kode_barang')
                    ->where(fn ($query) => $query->where('nup', (string) $this->input('nup')))
                    ->withoutTrashed(),
            ],
            'nup' => ['required', 'string', 'max:255'],
            'no_seri' => ['nullable', 'string', 'max:255'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'merek' => ['nullable', 'string', 'max:255'],
            'tahun_perolehan' => ['nullable', 'integer', 'min:1900', 'max:'.date('Y')],
            'nilai_perolehan' => ['nullable', 'numeric', 'min:0'],
            'tanggal_terima' => ['nullable', 'date', 'before_or_equal:today'],
            'jenis' => ['required', 'string', 'in:BMN-TIK,BMN-LAB,BMN-NON TIK,BMN-TIK & LAB'],
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
            'tanggal_terima.before_or_equal' => 'Tanggal terima tidak boleh melebihi tanggal hari ini (tidak bisa memilih tanggal di masa depan).',
            'tahun_perolehan.max' => 'Tahun perolehan tidak boleh melebihi tahun saat ini ('.date('Y').').',
            'kode_barang.required' => 'Kode Barang wajib diisi.',
            'kode_barang.digits' => 'Kode Barang harus berupa 10 digit angka (contoh: 3050104001).',
            'kode_barang.unique' => 'Kombinasi Kode Barang dan NUP sudah terdaftar dalam sistem. Silakan periksa kembali NUP atau Kode Barang.',
            'jenis.required' => 'Jenis Barang wajib dipilih.',
            'jenis.in' => 'Jenis Barang harus BMN-TIK, BMN-LAB, BMN-NON TIK, atau BMN-TIK & LAB.',
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
