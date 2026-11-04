<?php

namespace App\Http\Requests\Bmn;

use App\Models\Bmn;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBmnRequest extends FormRequest
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
        $bmn = $this->route('bmn');
        $bmnId = $bmn instanceof Bmn ? $bmn->id : $bmn;

        return [
            'kode_barang' => [
                'required',
                'string',
                'digits:10',
                Rule::unique('bmns', 'kode_barang')->ignore($bmnId),
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
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_terima.before_or_equal' => 'Tanggal terima tidak boleh melebihi tanggal hari ini (tidak bisa memilih tanggal di masa depan).',
            'tahun_perolehan.max' => 'Tahun perolehan tidak boleh melebihi tahun saat ini ('.date('Y').').',
            'kode_barang.required' => 'Kode Barang wajib diisi.',
            'kode_barang.digits' => 'Kode Barang harus berupa 10 digit angka (contoh: 3050104001 atau 3.05.01.04.001).',
            'kode_barang.unique' => 'Kode barang sudah terdaftar dalam sistem. Silakan gunakan kode barang yang lain.',
            'jenis.required' => 'Jenis Barang wajib dipilih.',
            'jenis.in' => 'Jenis Barang harus BMN-TIK, BMN-LAB, atau BMN-NON TIK.',
        ];
    }
}
