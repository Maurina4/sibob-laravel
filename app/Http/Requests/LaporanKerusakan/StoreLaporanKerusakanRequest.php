<?php

namespace App\Http\Requests\LaporanKerusakan;

use App\Models\LaporanKerusakan;
use Illuminate\Foundation\Http\FormRequest;

class StoreLaporanKerusakanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (empty($this->nomor)) {
            $countToday = LaporanKerusakan::whereDate('created_at', today())->count() + 1;
            $this->merge([
                'nomor' => 'LK/'.date('Ymd').'/'.str_pad((string) $countToday, 3, '0', STR_PAD_LEFT),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'nomor' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'bmn_id' => ['nullable', 'exists:bmns,id'],
            'pelapor_pegawai_id' => ['nullable', 'exists:pegawais,id'],
            'pelapor_nama' => ['nullable', 'string', 'max:255'],
            'pelapor_email' => ['nullable', 'string', 'max:255'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'lokasi' => ['required', 'string', 'max:255'],
            'tingkat' => ['required', 'in:Rusak Ringan,Rusak Berat,Ringan,Berat,Sedang'],
            'deskripsi' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.before_or_equal' => 'Tanggal temuan kerusakan tidak boleh melebihi tanggal hari ini (tidak bisa memilih tanggal di masa depan).',
            'tingkat.in' => 'Tingkat kerusakan harus Rusak Ringan atau Rusak Berat.',
        ];
    }
}
