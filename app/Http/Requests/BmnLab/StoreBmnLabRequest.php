<?php

namespace App\Http\Requests\BmnLab;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBmnLabRequest extends FormRequest
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
        return [
            'kode_barang' => [
                'required',
                'string',
                'digits:10',
                Rule::unique('bmns', 'kode_barang'),
                Rule::unique('bmns_lab', 'kode_barang'),
            ],
            'nup' => ['required', 'integer', 'min:1'],
            'no_seri' => ['nullable', 'string', 'max:255'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'merek' => ['nullable', 'string', 'max:255'],
            'tahun_perolehan' => ['nullable', 'integer', 'min:1900', 'max:'.date('Y')],
            'nilai_perolehan' => ['nullable', 'numeric', 'min:0'],
            'lokasi' => ['required', 'string', 'max:255'],
            'penanggungjawab' => ['required', 'string', 'max:255'],
            'kondisi' => ['nullable', 'string'],
            'status_kalibrasi' => ['nullable', 'string'],
            'tanggal_kalibrasi' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_kalibrasi.before_or_equal' => 'Tanggal kalibrasi tidak boleh melebihi tanggal hari ini (tidak bisa memilih tanggal di masa depan).',
            'tahun_perolehan.max' => 'Tahun perolehan tidak boleh melebihi tahun saat ini ('.date('Y').').',
            'kode_barang.required' => 'Kode Barang wajib diisi.',
            'kode_barang.digits' => 'Kode Barang harus berupa 10 digit angka (contoh: 3080101005 atau 3.08.01.01.005).',
            'kode_barang.unique' => 'Kode barang sudah terdaftar dalam sistem. Silakan gunakan kode barang yang lain.',
        ];
    }
}
