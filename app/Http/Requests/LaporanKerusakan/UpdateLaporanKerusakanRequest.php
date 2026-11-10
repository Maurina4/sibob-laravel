<?php

namespace App\Http\Requests\LaporanKerusakan;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLaporanKerusakanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isOperator = $this->user()?->role === 'Operator';

        return [
            'tanggal' => ['nullable', 'date', 'before_or_equal:today'],
            'bmn_id' => ['nullable', 'exists:bmns,id'],
            'pelapor_pegawai_id' => ['nullable', 'exists:pegawais,id'],
            'pelapor_nama' => ['nullable', 'string', 'max:255'],
            'pelapor_email' => ['nullable', 'string', 'max:255'],
            'nama_barang' => ['nullable', 'string', 'max:255'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'tingkat' => ['nullable', 'in:Rusak Berat,Berat'],
            'deskripsi' => ['nullable', 'string'],
            'status' => [$isOperator ? 'required' : 'nullable', 'in:Dilaporkan,Diterima,Diproses,Selesai,Ditolak'],
            'tanggapan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.before_or_equal' => 'Tanggal temuan kerusakan tidak boleh melebihi tanggal hari ini (tidak bisa memilih tanggal di masa depan).',
            'tingkat.in' => 'Tingkat kerusakan harus Rusak Berat.',
        ];
    }
}
