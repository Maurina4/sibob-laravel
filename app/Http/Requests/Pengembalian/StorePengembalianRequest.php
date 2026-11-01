<?php

namespace App\Http\Requests\Pengembalian;

use App\Models\Pengembalian;
use Illuminate\Foundation\Http\FormRequest;

class StorePengembalianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (empty($this->nomor_pengajuan)) {
            $countToday = Pengembalian::whereDate('created_at', today())->count() + 1;
            $this->merge([
                'nomor_pengajuan' => 'PL.03.01.22A.'.date('Ymd').'.'.str_pad((string) $countToday, 2, '0', STR_PAD_LEFT),
            ]);
        }

        if ($this->has('items') && is_array($this->items)) {
            $filtered = array_values(array_filter($this->items, function ($item) {
                return ! empty($item['bmn_id']) || ! empty($item['kondisi']);
            }));
            $this->merge(['items' => $filtered]);
        }
    }

    public function rules(): array
    {
        return [
            'nomor_pengajuan' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'nama_pengaju' => ['required', 'string', 'max:255'],
            'bidang' => ['required', 'string', 'max:255'],
            'kondisi_pengembalian' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.bmn_id' => ['required', 'exists:bmns,id'],
            'items.*.kondisi_awal' => ['nullable', 'string', 'max:255'],
            'items.*.kondisi' => ['required', 'string', 'max:255'],
            'items.*.keterangan' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'nomor_pengajuan.required' => 'Nomor pengajuan wajib diisi.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.before_or_equal' => 'Tanggal pengembalian tidak boleh melebihi tanggal hari ini (tidak bisa memilih tanggal di masa depan).',
            'nama_pengaju.required' => 'Nama pengaju wajib dipilih.',
            'bidang.required' => 'Bidang wajib dipilih.',
            'items.required' => 'Minimal satu barang harus dipilih untuk dikembalikan.',
            'items.min' => 'Minimal satu barang harus dipilih untuk dikembalikan.',
            'items.*.bmn_id.required' => 'Barang wajib dipilih.',
            'items.*.bmn_id.exists' => 'Barang yang dipilih tidak valid.',
            'items.*.kondisi.required' => 'Kondisi barang wajib diisi.',
        ];
    }
}
