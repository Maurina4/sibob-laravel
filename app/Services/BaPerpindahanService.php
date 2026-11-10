<?php

namespace App\Services;

use App\Models\BaPerpindahan;
use App\Models\Bmn;
use App\Models\BmnAngkutan;
use App\Models\BmnLab;
use App\Models\BmnTik;
use App\Models\DataTerhapus;
use App\Models\Lokasi;
use App\Models\Pegawai;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BaPerpindahanService
{
    public function getPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return BaPerpindahan::query()
            ->with(['bmn', 'pegawai', 'pengajuPegawai', 'pihakPertamaPegawai'])
            ->active()
            ->filter($filters)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function store(array $validated, $user): BaPerpindahan
    {
        if (! empty($validated['bmn_id'])) {
            $bmn = Bmn::find($validated['bmn_id']);
            if ($bmn) {
                if (empty($validated['nama_barang'])) {
                    $validated['nama_barang'] = $bmn->nama_barang;
                }
                if (empty($validated['dari'])) {
                    $validated['dari'] = $bmn->lokasi;
                }
                // Pihak pertama (pemegang BMN saat ini yang menyerahkan barang)
                if (empty($validated['pihak_pertama']) && ! empty($bmn->penanggungjawab)) {
                    $validated['pihak_pertama'] = $bmn->penanggungjawab;
                }
                if (empty($validated['pihak_pertama_id']) && ! empty($bmn->penanggung_jawab_id)) {
                    $validated['pihak_pertama_id'] = $bmn->penanggung_jawab_id;
                }
            }
        }

        // Resolusi Pegawai Pengaju (berelasi langsung ke tabel pegawais)
        $pengajuPegawai = null;
        if (! empty($validated['pengaju_pegawai_id'])) {
            $pengajuPegawai = Pegawai::find($validated['pengaju_pegawai_id']);
        } elseif (! empty($validated['pegawai_id'])) {
            $pengajuPegawai = Pegawai::find($validated['pegawai_id']);
        } elseif (! empty($validated['pengaju_nama'])) {
            $pengajuPegawai = Pegawai::where('nama', $validated['pengaju_nama'])->first();
        }

        if ($pengajuPegawai) {
            $validated['pengaju_pegawai_id'] = $pengajuPegawai->id;
            $validated['pegawai_id'] = $pengajuPegawai->id;
            $validated['pengaju_nama'] = $pengajuPegawai->nama;
        } elseif (! empty($validated['pengaju_nama'])) {
            // Pengaju nama telah diberikan eksplisit
            $validated['pengaju_nama'] = $validated['pengaju_nama'];
        } else {
            // Coba cocokkan user login dengan pegawai
            $matchedPegawai = Pegawai::where('nama', $user?->name)->orWhere('email', $user?->email)->first();
            if ($matchedPegawai) {
                $validated['pengaju_pegawai_id'] = $matchedPegawai->id;
                $validated['pegawai_id'] = $matchedPegawai->id;
                $validated['pengaju_nama'] = $matchedPegawai->nama;
            } elseif (! empty($validated['pihak_pertama'])) {
                // Fallback untuk backward compatibility jika tidak ada pengaju eksplisit
                $validated['pengaju_nama'] = $validated['pihak_pertama'];
                $validated['pengaju_pegawai_id'] = $validated['pihak_pertama_id'] ?? null;
                $validated['pegawai_id'] = $validated['pihak_pertama_id'] ?? null;
            } else {
                $validated['pengaju_nama'] = $user?->name ?? 'Pegawai';
            }
        }

        // Pastikan pihak_pertama_id dan pihak_pertama konsisten
        if (! empty($validated['pihak_pertama']) && empty($validated['pihak_pertama_id'])) {
            $pjPegawai = Pegawai::where('nama', $validated['pihak_pertama'])->first();
            if ($pjPegawai) {
                $validated['pihak_pertama_id'] = $pjPegawai->id;
            }
        } elseif (empty($validated['pihak_pertama']) && ! empty($validated['pihak_pertama_id'])) {
            $pjPegawai = Pegawai::find($validated['pihak_pertama_id']);
            if ($pjPegawai) {
                $validated['pihak_pertama'] = $pjPegawai->nama;
            }
        }

        $validated['pengaju_email'] = $user?->email;
        $validated['pengaju_id'] = $user?->id;

        if ($user?->role !== 'Operator') {
            $validated['pihak_kedua'] = null; // Hanya dapat ditentukan oleh Operator
        }

        if (! empty($validated['dari'])) {
            $lokasiAsal = Lokasi::findByNameOrCode($validated['dari']);
            if ($lokasiAsal) {
                $validated['lokasi_asal_id'] = $lokasiAsal->id;
                $validated['dari'] = $lokasiAsal->nama;
            }
        }

        if (! empty($validated['lokasi_tujuan_id'])) {
            $lokasiTujuan = Lokasi::find($validated['lokasi_tujuan_id']);
            if ($lokasiTujuan) {
                $validated['ke'] = $lokasiTujuan->nama;
            }
        } elseif (! empty($validated['ke'])) {
            $lokasiTujuan = Lokasi::findByNameOrCode($validated['ke']);
            if ($lokasiTujuan) {
                $validated['lokasi_tujuan_id'] = $lokasiTujuan->id;
                $validated['ke'] = $lokasiTujuan->nama;
            }
        }

        if (empty($validated['nomor_ba'])) {
            $countToday = BaPerpindahan::whereDate('created_at', today())->count() + 1;
            $validated['nomor_ba'] = 'PL.03.01.22A.'.date('Ymd').'.'.str_pad((string) $countToday, 2, '0', STR_PAD_LEFT);
        }

        $validated['status'] = 'Diajukan';

        return BaPerpindahan::create($validated);
    }

    public function update(BaPerpindahan $baPerpindahan, array $validated, $user = null): bool
    {
        if ($user && $user->role !== 'Operator') {
            unset($validated['pihak_kedua']);
        }

        if (! empty($validated['bmn_id'])) {
            $bmn = Bmn::find($validated['bmn_id']);
            if ($bmn) {
                if (empty($validated['nama_barang'])) {
                    $validated['nama_barang'] = $bmn->nama_barang;
                }
                if (empty($validated['dari'])) {
                    $validated['dari'] = $bmn->lokasi;
                }
                if (empty($validated['pihak_pertama']) && ! empty($bmn->penanggungjawab)) {
                    $validated['pihak_pertama'] = $bmn->penanggungjawab;
                }
                if (empty($validated['pihak_pertama_id']) && ! empty($bmn->penanggung_jawab_id)) {
                    $validated['pihak_pertama_id'] = $bmn->penanggung_jawab_id;
                }
            }
        }

        // Resolusi Pegawai Pengaju
        if (! empty($validated['pengaju_pegawai_id'])) {
            $pengajuPegawai = Pegawai::find($validated['pengaju_pegawai_id']);
            if ($pengajuPegawai) {
                $validated['pengaju_nama'] = $pengajuPegawai->nama;
                $validated['pegawai_id'] = $pengajuPegawai->id;
            }
        } elseif (! empty($validated['pegawai_id'])) {
            $pengajuPegawai = Pegawai::find($validated['pegawai_id']);
            if ($pengajuPegawai) {
                $validated['pengaju_pegawai_id'] = $pengajuPegawai->id;
                $validated['pengaju_nama'] = $pengajuPegawai->nama;
            }
        } elseif (! empty($validated['pengaju_nama'])) {
            $pengajuPegawai = Pegawai::where('nama', $validated['pengaju_nama'])->first();
            if ($pengajuPegawai) {
                $validated['pengaju_pegawai_id'] = $pengajuPegawai->id;
                $validated['pegawai_id'] = $pengajuPegawai->id;
            }
        }

        if (! empty($validated['pihak_pertama']) && empty($validated['pihak_pertama_id'])) {
            $pjPegawai = Pegawai::where('nama', $validated['pihak_pertama'])->first();
            if ($pjPegawai) {
                $validated['pihak_pertama_id'] = $pjPegawai->id;
            }
        }

        if (! empty($validated['dari'])) {
            $lokasiAsal = Lokasi::findByNameOrCode($validated['dari']);
            if ($lokasiAsal) {
                $validated['lokasi_asal_id'] = $lokasiAsal->id;
                $validated['dari'] = $lokasiAsal->nama;
            }
        }

        if (! empty($validated['lokasi_tujuan_id'])) {
            $lokasiTujuan = Lokasi::find($validated['lokasi_tujuan_id']);
            if ($lokasiTujuan) {
                $validated['ke'] = $lokasiTujuan->nama;
            }
        } elseif (! empty($validated['ke'])) {
            $lokasiTujuan = Lokasi::findByNameOrCode($validated['ke']);
            if ($lokasiTujuan) {
                $validated['lokasi_tujuan_id'] = $lokasiTujuan->id;
                $validated['ke'] = $lokasiTujuan->nama;
            }
        }

        return $baPerpindahan->update($validated);
    }

    public function delete(BaPerpindahan $baPerpindahan, ?string $userEmail): bool
    {
        $waktuHapus = now();
        $penghapus = $userEmail ?? 'system';

        return DB::transaction(function () use ($baPerpindahan, $waktuHapus, $penghapus) {
            $baPerpindahan->update([
                'dihapus' => true,
                'dihapus_pada' => $waktuHapus,
                'dihapus_oleh' => $penghapus,
            ]);
            $baPerpindahan->delete();

            DataTerhapus::create([
                'jenis' => 'BA Perpindahan',
                'data_id' => $baPerpindahan->id,
                'bmn_id' => $baPerpindahan->bmn_id,
                'nama' => $baPerpindahan->nomor_ba ?: ('BA-MUT/'.$baPerpindahan->id),
                'dihapus_pada' => $waktuHapus,
                'dihapus_oleh' => $penghapus,
            ]);

            return true;
        });
    }

    public function approve(BaPerpindahan $baPerpindahan, $user, ?string $catatan = null, ?string $pihakKedua = null): bool
    {
        $updateData = [
            'status' => 'Disetujui',
            'catatan_operator' => $catatan,
            'tanggal_keputusan' => now(),
            'diputuskan_oleh' => $user?->name ?? 'Operator',
        ];

        if (! empty($pihakKedua)) {
            $updateData['pihak_kedua'] = $pihakKedua;
        }

        $result = $baPerpindahan->update($updateData);

        if ($baPerpindahan->bmn_id) {
            $bmn = $baPerpindahan->bmn;
            if ($bmn) {
                $bmnUpdate = [];
                if (! empty($baPerpindahan->ke)) {
                    $bmnUpdate['lokasi'] = $baPerpindahan->ke;
                    $lokasiModel = Lokasi::where('nama', $baPerpindahan->ke)->first();
                    if ($lokasiModel) {
                        $bmnUpdate['lokasi_id'] = $lokasiModel->id;
                    }
                }
                $finalPihakKedua = $pihakKedua ?? $baPerpindahan->pihak_kedua;
                if (! empty($finalPihakKedua)) {
                    $bmnUpdate['penanggungjawab'] = $finalPihakKedua;
                    $pegawaiModel = null;
                    if (preg_match('/\((\d+)\)/', $finalPihakKedua, $matches)) {
                        $pegawaiModel = Pegawai::where('nip', $matches[1])->first();
                    }
                    if (! $pegawaiModel) {
                        $cleanName = trim(preg_replace('/\s*\(.*?\)\s*/', '', $finalPihakKedua));
                        $pegawaiModel = Pegawai::where('nama', $cleanName)->orWhere('nama', $finalPihakKedua)->first();
                    }
                    if ($pegawaiModel) {
                        $bmnUpdate['penanggung_jawab_id'] = $pegawaiModel->id;
                        if (! empty($pegawaiModel->nip)) {
                            $bmnUpdate['penanggungjawab_nip'] = $pegawaiModel->nip;
                        }
                    }
                }
                if (! empty($bmnUpdate)) {
                    $bmnUpdate['is_dikembalikan'] = false;
                    $bmn->update($bmnUpdate);

                    if (! empty($bmn->kode_barang) && ! empty($bmn->nup)) {
                        $subUpdate = ['is_dikembalikan' => false];
                        if (isset($bmnUpdate['lokasi'])) {
                            $subUpdate['lokasi'] = $bmnUpdate['lokasi'];
                        }
                        if (isset($bmnUpdate['penanggungjawab'])) {
                            $subUpdate['penanggungjawab'] = $bmnUpdate['penanggungjawab'];
                        }
                        BmnTik::where('kode_barang', $bmn->kode_barang)
                            ->where('nup', $bmn->nup)
                            ->update($subUpdate);

                        BmnLab::where('kode_barang', $bmn->kode_barang)
                            ->where('nup', $bmn->nup)
                            ->update($subUpdate);

                        BmnAngkutan::where('kode_barang', $bmn->kode_barang)
                            ->where('nup', $bmn->nup)
                            ->update($subUpdate);
                    }
                }
            }
        }

        return $result;
    }

    public function cancelDecision(BaPerpindahan $baPerpindahan, $user, ?string $catatan = null): bool
    {
        $previousStatus = $baPerpindahan->status;
        $label = $previousStatus === 'Disetujui' ? 'Persetujuan' : ($previousStatus === 'Ditolak' ? 'Penolakan' : 'Keputusan');

        $updateData = [
            'status' => 'Dibatalkan',
            'catatan_operator' => $catatan ? "{$label} dibatalkan: {$catatan}" : "{$label} dibatalkan oleh Operator",
            'tanggal_keputusan' => now(),
            'diputuskan_oleh' => $user?->name ?? 'Operator',
        ];

        $result = $baPerpindahan->update($updateData);

        if ($previousStatus === 'Disetujui' && $baPerpindahan->bmn_id) {
            $bmn = $baPerpindahan->bmn;
            if ($bmn) {
                $bmnRevert = [];
                if (! empty($baPerpindahan->dari)) {
                    $bmnRevert['lokasi'] = $baPerpindahan->dari;
                    $lokasiModel = Lokasi::where('nama', $baPerpindahan->dari)->first();
                    if ($lokasiModel) {
                        $bmnRevert['lokasi_id'] = $lokasiModel->id;
                    }
                }
                $revertPjName = $baPerpindahan->pihak_pertama ?: $baPerpindahan->pengaju_nama;
                $revertPjId = $baPerpindahan->pihak_pertama_id ?: $baPerpindahan->pegawai_id;

                if (! empty($revertPjName)) {
                    $bmnRevert['penanggungjawab'] = $revertPjName;
                }
                if ($revertPjId) {
                    $bmnRevert['penanggung_jawab_id'] = $revertPjId;
                    $revertPegawai = Pegawai::find($revertPjId);
                    if ($revertPegawai && ! empty($revertPegawai->nip)) {
                        $bmnRevert['penanggungjawab_nip'] = $revertPegawai->nip;
                    }
                }
                if (! empty($bmnRevert)) {
                    $bmn->update($bmnRevert);
                }
            }
        }

        return $result;
    }

    public function cancelApproval(BaPerpindahan $baPerpindahan, $user, ?string $catatan = null): bool
    {
        return $this->cancelDecision($baPerpindahan, $user, $catatan);
    }

    public function reject(BaPerpindahan $baPerpindahan, $user, ?string $catatan = null, ?string $pihakKedua = null): bool
    {
        $updateData = [
            'status' => 'Ditolak',
            'catatan_operator' => $catatan,
            'tanggal_keputusan' => now(),
            'diputuskan_oleh' => $user?->name ?? 'Operator',
        ];

        if (! empty($pihakKedua)) {
            $updateData['pihak_kedua'] = $pihakKedua;
        }

        return $baPerpindahan->update($updateData);
    }
}
