<?php

namespace App\Http\Requests\Lokasi;

use App\Models\Lokasi;
use App\Services\LokasiService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLokasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $kode = $this->input('kode_ruangan');
        if ($kode !== null) {
            $kode = trim($kode);
            $kode = app(LokasiService::class)->normalizeKodeRuangan($kode);
        }

        $nama = $this->input('nama');
        if ($nama !== null) {
            $nama = trim($nama);
        }

        $this->merge([
            'kode_ruangan' => $kode ?: null,
            'nama' => $nama,
        ]);
    }

    public function rules(): array
    {
        return [
            'kode_ruangan' => [
                'nullable',
                'string',
                'max:50',
                'different:nama',
            ],
            'nama' => [
                'required',
                'string',
                'max:255',
                'different:kode_ruangan',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $lokasi = $this->route('lokasi');
            $lokasiId = $lokasi instanceof Lokasi ? $lokasi->id : (int) $lokasi;
            $lokasiModel = $lokasi instanceof Lokasi ? $lokasi : Lokasi::find($lokasiId);

            $kode = trim((string) $this->input('kode_ruangan', ''));
            $nama = trim((string) $this->input('nama', ''));

            // 1. Kode ruangan dan nama lokasi tidak boleh bernilai sama
            if (! empty($kode) && ! empty($nama) && strcasecmp($kode, $nama) === 0) {
                $validator->errors()->add('kode_ruangan', 'Kode ruangan dan nama lokasi tidak boleh sama.');
                $validator->errors()->add('nama', 'Nama lokasi tidak boleh sama dengan kode ruangan.');
            }

            // 2. Kode ruangan tidak boleh sama dengan lokasi lain
            if (! empty($kode)) {
                $isSameCodeAsCurrent = $lokasiModel && strcasecmp($kode, (string) $lokasiModel->kode_ruangan) === 0;
                if (! $isSameCodeAsCurrent) {
                    $existsKode = Lokasi::whereRaw('LOWER(kode_ruangan) = ?', [strtolower($kode)])
                        ->where('id', '!=', $lokasiId)
                        ->exists();
                    if ($existsKode) {
                        $validator->errors()->add('kode_ruangan', "Kode ruangan '{$kode}' sudah digunakan oleh lokasi lain.");
                    }
                }
            }

            // 3. Nama lokasi tidak boleh sama dengan lokasi lain
            if (! empty($nama)) {
                $isSameNameAsCurrent = $lokasiModel && strcasecmp($nama, (string) $lokasiModel->nama) === 0;
                if (! $isSameNameAsCurrent) {
                    $existsNama = Lokasi::whereRaw('LOWER(nama) = ?', [strtolower($nama)])
                        ->where('id', '!=', $lokasiId)
                        ->exists();
                    if ($existsNama) {
                        $validator->errors()->add('nama', "Nama lokasi '{$nama}' sudah terdaftar pada master lokasi.");
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama lokasi wajib diisi.',
            'nama.different' => 'Nama lokasi tidak boleh sama dengan kode ruangan.',
            'kode_ruangan.different' => 'Kode ruangan dan nama lokasi tidak boleh sama.',
        ];
    }
}

