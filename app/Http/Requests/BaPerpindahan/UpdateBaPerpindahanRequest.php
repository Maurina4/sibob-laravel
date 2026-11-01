<?php

namespace App\Http\Requests\BaPerpindahan;

use App\Models\BaPerpindahan;
use App\Models\Bmn;
use App\Models\Pegawai;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBaPerpindahanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nomor_ba' => ['nullable', 'string', 'max:255'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'bmn_id' => ['nullable', 'exists:bmns,id'],
            'pegawai_id' => ['nullable', 'exists:pegawais,id'],
            'pengaju_pegawai_id' => ['nullable', 'exists:pegawais,id'],
            'pihak_pertama' => ['nullable', 'string', 'max:255'],
            'pihak_pertama_id' => ['nullable', 'exists:pegawais,id'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'dari' => ['required', 'string', 'max:255'],
            'ke' => ['nullable', 'string', 'max:255'],
            'lokasi_tujuan_id' => ['nullable', 'exists:lokasis,id'],
            'alasan' => ['required', 'string'],
            'pengaju_nama' => ['nullable', 'string', 'max:255'],
            'pihak_kedua' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $pihakKedua = $this->input('pihak_kedua');
            if (empty($pihakKedua)) {
                return;
            }

            $pihakPertama = $this->input('pihak_pertama');
            $pihakPertamaId = $this->input('pihak_pertama_id');

            if (empty($pihakPertama) && $this->filled('bmn_id')) {
                $bmn = Bmn::find($this->input('bmn_id'));
                if ($bmn) {
                    $pihakPertama = $bmn->penanggungjawab;
                    $pihakPertamaId = $pihakPertamaId ?: $bmn->penanggung_jawab_id;
                }
            }

            if (empty($pihakPertama) && $this->route('baPerpindahan')) {
                $ba = $this->route('baPerpindahan');
                $pihakPertama = $ba->pihak_pertama ?: ($ba->bmn?->penanggungjawab);
                $pihakPertamaId = $pihakPertamaId ?: ($ba->pihak_pertama_id ?: $ba->bmn?->penanggung_jawab_id);
            }

            if (BaPerpindahan::isSamePerson($pihakPertama, $pihakKedua, $pihakPertamaId ? (int) $pihakPertamaId : null)) {
                $validator->errors()->add('pihak_kedua', 'Penerima (Pihak Kedua) tidak boleh sama dengan Penyerah (Pihak Pertama).');
            }

            $pengajuNama = $this->input('pengaju_nama');
            $pengajuPegawaiId = $this->input('pengaju_pegawai_id') ?: $this->input('pegawai_id');
            if (empty($pengajuNama) && $pengajuPegawaiId) {
                $pengaju = Pegawai::find($pengajuPegawaiId);
                $pengajuNama = $pengaju?->nama;
            }
            if (empty($pengajuNama) && $this->route('baPerpindahan')) {
                $ba = $this->route('baPerpindahan');
                $pengajuNama = $ba->pengajuPegawai?->nama ?: $ba->pengaju_nama;
                $pengajuPegawaiId = $pengajuPegawaiId ?: ($ba->pengaju_pegawai_id ?: $ba->pegawai_id);
            }

            if (BaPerpindahan::isSamePerson($pengajuNama, $pihakKedua, $pengajuPegawaiId ? (int) $pengajuPegawaiId : null)) {
                $validator->errors()->add('pihak_kedua', 'Penerima (Pihak Kedua) tidak boleh sama dengan Pegawai Pengaju.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'tanggal.before_or_equal' => 'Tanggal pemindahan tidak boleh melebihi tanggal hari ini (tidak bisa memilih tanggal di masa depan).',
        ];
    }
}
