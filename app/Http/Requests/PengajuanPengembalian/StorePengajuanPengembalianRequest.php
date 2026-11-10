<?php

namespace App\Http\Requests\PengajuanPengembalian;

use App\Models\Bmn;
use App\Models\PengajuanPengembalianItem;
use Illuminate\Foundation\Http\FormRequest;

class StorePengajuanPengembalianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $rules = [
            'alasan' => ['required', 'in:pengembalian_biasa,rusak_berat'],
            'tanggal' => ['nullable', 'date', 'before_or_equal:today'],
            'bidang' => ['required', 'string', 'max:255'],
            'nomor_sipanda' => ['nullable', 'string', 'max:255'],
            'catatan_pemohon' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.bmn_id' => ['required', 'distinct', 'exists:bmns,id'],
            'items.*.keterangan' => ['nullable', 'string'],
        ];

        if ($this->input('alasan') === 'rusak_berat') {
            $rules['items.*.deskripsi_kerusakan'] = ['required', 'string', 'min:5'];
            $rules['items.*.tanggal_temuan'] = ['nullable', 'date', 'before_or_equal:today'];
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);
            $alasan = $this->input('alasan');

            if (empty($items)) {
                return;
            }

            $bmnIds = array_filter(array_column($items, 'bmn_id'));

            // 1. Cek jika barang sedang dalam pengajuan lain yang berstatus 'diajukan'
            $pendingBmnIds = PengajuanPengembalianItem::whereIn('bmn_id', $bmnIds)
                ->whereHas('pengajuan', fn ($q) => $q->where('status', 'diajukan'))
                ->pluck('bmn_id')
                ->toArray();

            if (! empty($pendingBmnIds)) {
                $pendingNames = Bmn::whereIn('id', $pendingBmnIds)->pluck('nama_barang')->join(', ');
                $validator->errors()->add('items', "Barang berikut ({$pendingNames}) sedang dalam proses pengajuan lain yang berstatus Diajukan.");
                foreach ($items as $idx => $it) {
                    if (in_array($it['bmn_id'] ?? null, $pendingBmnIds)) {
                        $validator->errors()->add("items.{$idx}.bmn_id", 'Barang ini sedang dalam proses pengajuan lain yang berstatus Diajukan.');
                    }
                }
            }

            // 2. Cek kondisi barang
            $bmnModels = Bmn::whereIn('id', $bmnIds)->get()->keyBy('id');

            foreach ($items as $idx => $item) {
                $bmn = $bmnModels[$item['bmn_id']] ?? null;
                if (! $bmn) {
                    continue;
                }

                $kondisi = strtolower(trim($bmn->kondisi ?? ''));

                // Barang rusak ringan dilarang
                if ($kondisi === 'rusak ringan') {
                    $validator->errors()->add("items.{$idx}.bmn_id", "Barang '{$bmn->nama_barang}' berkondisi Rusak Ringan tidak dapat diajukan (diperbaiki langsung tanpa dikembalikan).");
                }

                // Untuk pengembalian biasa, hanya boleh barang kondisi Baik
                if ($alasan === 'pengembalian_biasa' && ! in_array($kondisi, ['baik', ''])) {
                    $validator->errors()->add("items.{$idx}.bmn_id", "Untuk pengembalian biasa, hanya barang berkondisi Baik yang dapat dipilih (kondisi saat ini: {$bmn->kondisi}).");
                }

                // Pembatasan kepemilikan: Pengguna non-Operator hanya dapat mengajukan BMN miliknya sendiri
                if (auth()->check() && auth()->user()->role !== 'Operator' && ! $bmn->isOwnedBy(auth()->user())) {
                    $validator->errors()->add("items.{$idx}.bmn_id", "Anda hanya dapat mengajukan pengembalian untuk BMN yang menjadi tanggung jawab Anda.");
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'alasan.required' => 'Pilih alasan pengembalian (Pengembalian Biasa atau Rusak Berat).',
            'alasan.in' => 'Alasan pengembalian tidak valid.',
            'tanggal.required' => 'Tanggal pengajuan wajib diisi.',
            'tanggal.before_or_equal' => 'Tanggal pengajuan tidak boleh melebihi tanggal hari ini.',
            'bidang.required' => 'Bidang / unit kerja wajib dipilih.',
            'items.required' => 'Minimal pilih 1 barang untuk diajukan.',
            'items.min' => 'Minimal pilih 1 barang untuk diajukan.',
            'items.*.bmn_id.distinct' => 'Terdapat barang ganda yang dipilih dalam formulir ini.',
            'items.*.deskripsi_kerusakan.required' => 'Deskripsi kerusakan dan kronologi wajib diisi untuk barang rusak berat.',
            'items.*.deskripsi_kerusakan.min' => 'Deskripsi kerusakan minimal 5 karakter.',
            'items.*.tanggal_temuan.before_or_equal' => 'Tanggal temuan tidak boleh melebihi tanggal hari ini.',
        ];
    }
}
