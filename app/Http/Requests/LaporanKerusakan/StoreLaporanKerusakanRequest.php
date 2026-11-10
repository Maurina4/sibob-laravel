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
            'tingkat' => ['required', 'in:Rusak Berat,Berat'],
            'deskripsi' => ['required', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = auth()->user();
            if ($user && $user->role !== 'Operator' && $this->filled('bmn_id')) {
                $bmn = \App\Models\Bmn::find($this->input('bmn_id'));
                if ($bmn && ! $bmn->isOwnedBy($user, false, $this->input('pelapor_pegawai_id') ? (int) $this->input('pelapor_pegawai_id') : null)) {
                    $validator->errors()->add('bmn_id', 'Anda hanya dapat melaporkan kerusakan untuk BMN yang menjadi tanggung jawab Anda.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'tanggal.before_or_equal' => 'Tanggal temuan kerusakan tidak boleh melebihi tanggal hari ini (tidak bisa memilih tanggal di masa depan).',
            'tingkat.in' => 'Tingkat kerusakan harus Rusak Berat.',
        ];
    }
}
