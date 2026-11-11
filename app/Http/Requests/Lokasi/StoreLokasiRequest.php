<?php

namespace App\Http\Requests\Lokasi;

use App\Models\Lokasi;
use App\Services\LokasiService;
use Illuminate\Foundation\Http\FormRequest;

class StoreLokasiRequest extends FormRequest
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
            $kode = trim((string) $this->input('kode_ruangan', ''));
            $nama = trim((string) $this->input('nama', ''));

            // 1. Kode ruangan dan nama lokasi tidak boleh bernilai sama
            if (! empty($kode) && ! empty($nama) && strcasecmp($kode, $nama) === 0) {
                $validator->errors()->add('kode_ruangan', 'Kode ruangan dan nama lokasi tidak boleh sama.');
                $validator->errors()->add('nama', 'Nama lokasi tidak boleh sama dengan kode ruangan.');
            }

            // 2. Kode ruangan tidak boleh sama dengan kode ruangan yang sudah ada
            if (! empty($kode)) {
                $existsKode = Lokasi::whereRaw('LOWER(kode_ruangan) = ?', [strtolower($kode)])->exists();
                if ($existsKode) {
                    $validator->errors()->add('kode_ruangan', "Kode ruangan '{$kode}' sudah digunakan oleh lokasi lain.");
                }
            }

            // 3. Nama lokasi tidak boleh sama dengan nama lokasi yang sudah ada
            if (! empty($nama)) {
                $existsNama = Lokasi::whereRaw('LOWER(nama) = ?', [strtolower($nama)])->exists();
                if ($existsNama) {
                    $validator->errors()->add('nama', "Nama lokasi '{$nama}' sudah terdaftar pada master lokasi.");
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

