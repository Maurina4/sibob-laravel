<?php

namespace App\Http\Requests\BmnAngkutan;

use App\Models\BmnAngkutan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBmnAngkutanRequest extends FormRequest
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
        if ($this->has('no_polisi') && is_string($this->no_polisi)) {
            $merge['no_polisi'] = strtoupper(trim($this->no_polisi));
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
        $bmnAngkutan = $this->route('bmnAngkutan');
        $angkutan = $bmnAngkutan instanceof BmnAngkutan ? $bmnAngkutan : BmnAngkutan::find($bmnAngkutan);
        $angkutanId = $angkutan?->id ?? $bmnAngkutan;
        $bmnId = $angkutan?->bmn_id ?? $angkutan?->bmn?->id;

        return [
            'kode_barang' => [
                'required',
                'string',
                'digits:10',
                Rule::unique('bmns', 'kode_barang')
                    ->where(fn ($query) => $query->where('nup', (string) $this->input('nup')))
                    ->withoutTrashed()
                    ->ignore($bmnId),
                Rule::unique('bmns_angkutan', 'kode_barang')
                    ->where(fn ($query) => $query->where('nup', (int) $this->input('nup'))->where('dihapus', 0))
                    ->ignore($angkutanId),
            ],
            'nup' => ['required', 'integer', 'min:1'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'merek' => ['nullable', 'string', 'max:255'],
            'no_polisi' => ['required', 'string', 'max:50'],
            'tahun_perolehan' => ['nullable', 'integer', 'min:1900', 'max:'.date('Y')],
            'nilai_perolehan' => ['nullable', 'numeric', 'min:0'],
            'lokasi' => ['required', 'string', 'max:255'],
            'penanggungjawab' => ['required', 'string', 'max:255'],
            'kondisi' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'no_polisi.required' => 'Nomor Polisi (Plat Nomor) wajib diisi.',
            'tahun_perolehan.max' => 'Tahun perolehan tidak boleh melebihi tahun saat ini ('.date('Y').').',
            'kode_barang.required' => 'Kode Barang wajib diisi.',
            'kode_barang.digits' => 'Kode Barang harus berupa 10 digit angka (contoh: 3020101001).',
            'kode_barang.unique' => 'Kombinasi Kode Barang dan NUP sudah terdaftar dalam sistem. Silakan periksa kembali NUP atau Kode Barang.',
        ];
    }
}
