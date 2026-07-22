<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Upt;
use App\Models\DataRegistrasi;
use App\Models\DataResidivis;
use App\Models\DataPemindahan;
use App\Models\DataPembinaanKepribadian;
use App\Models\DataPembinaanKemandirian;
use App\Models\DataIntegrasi;
use App\Exports\RekapIntegrasiExport;

class DashboardPembinaanController extends Controller
{
    private function getMasterData()
    {
        return [
            'umums' => \App\Models\RegistrasiUmum::all(),
            'pidsuses' => \App\Models\RegistrasiPidsus::all(),
            'pidums' => \App\Models\RegistrasiPidum::all(),
            'overstayings' => \App\Models\RegistrasiOverstaying::all(),
            'integrasis' => \App\Models\JenisIntegrasi::all(),
            'identitases' => \App\Models\RegistrasiIdentitas::all(),
            'agamas' => \App\Models\JenisAgama::all(),
            'pengeluarans' => \App\Models\JenisPengeluaran::all(),
            'pendidikans' => \App\Models\RegistrasiPendidikan::all(),
            'detail_napis' => \App\Models\RegistrasiDetailNapi::all(),
            'detail_tahanans' => \App\Models\RegistrasiDetailTahanan::all(),
            'petugases' => \App\Models\RegistrasiPetugas::all(),
            'pengunjungs' => \App\Models\RegistrasiPengunjung::all(),
            'wbp_dikunjungis' => \App\Models\RegistrasiWbpDikunjungi::all(),
            'wbp_vidcalls' => \App\Models\RegistrasiWbpVidcall::all(),
            'wbp_wartels' => \App\Models\RegistrasiWbpWartel::all(),
            'barang_titipans' => \App\Models\RegistrasiBarangTitipan::all(),
        ];
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $isUptUser = !empty($user->upt_id); // 🛠️ Deteksi apakah user orang UPT
        
        $tanggal = $request->tanggal ?? date('Y-m-d'); 
        // 🛠️ Paksa $uptId ke UPT user jika dia orang UPT, kalau bukan ambil dari request
        $uptId = $isUptUser ? $user->upt_id : $request->upt_id; 
        $modul = $request->modul ?? 'ringkasan';
        
        $qRegistrasi = DataRegistrasi::with('upt')->whereDate('tanggal', $tanggal);
        if ($uptId) $qRegistrasi->where('upt_id', $uptId);
        $dataRegistrasi = $qRegistrasi->get();

        $qResidivis = DataResidivis::with(['upt', 'jenis_pidana_sekarang'])->whereDate('tanggal', $tanggal);
        if ($uptId) $qResidivis->where('upt_id', $uptId);
        $dataResidivis = $qResidivis->get();

        $qPemindahan = DataPemindahan::with(['uptAsal', 'uptTujuan', 'jenisPemindahan'])->whereDate('tanggal_pelaksanaan', $tanggal);
        if ($uptId) $qPemindahan->where('upt_id', $uptId);
        $dataPemindahan = $qPemindahan->get();

        $qIntegrasi = DataIntegrasi::with('upt')->whereDate('tanggal_input', $tanggal);
        if ($uptId) $qIntegrasi->where('upt_id', $uptId);
        $dataIntegrasi = $qIntegrasi->get();

        $qKepribadian = DataPembinaanKepribadian::whereDate('tanggal', $tanggal);
        if ($uptId) $qKepribadian->where('upt_id', $uptId);
        $dataKepribadian = $qKepribadian->get();

        $qKemandirian = DataPembinaanKemandirian::whereDate('tanggal', $tanggal);
        if ($uptId) $qKemandirian->where('upt_id', $uptId);
        $dataKemandirian = $qKemandirian->get();

        $highlight = [
            'total_residivis' => $dataResidivis->count(),
            'total_pemindahan' => $dataPemindahan->count(),
            'kegiatan_kepribadian' => $dataKepribadian->count(),
            'kegiatan_kemandirian' => $dataKemandirian->count(),
            'total_peserta_kepribadian' => $dataKepribadian->sum('jumlah_peserta'),
            'total_peserta_kemandirian' => $dataKemandirian->sum('jumlah_peserta'),
            'total_integrasi' => $dataIntegrasi->sum('jumlah_pb') + 
                                 $dataIntegrasi->sum('jumlah_cb') + 
                                 $dataIntegrasi->sum('jumlah_cmb') + 
                                 $dataIntegrasi->sum('jumlah_asimilasi') + 
                                 $dataIntegrasi->sum('jumlah_bebas_murni') + 
                                 $dataIntegrasi->sum('jumlah_cmk'),
        ];

        $dataDinamis = [];
        
        if ($modul === 'registrasi') {
            if (empty($uptId) && $dataRegistrasi->count() > 0) {
                $rekapFields = [
                    'rekap_umum', 'rekap_pidsus', 'rekap_pidum', 'rekap_overstaying', 
                    'rekap_integrasi', 'rekap_identitas', 'rekap_agama', 'rekap_pengeluaran',
                    'rekap_pendidikan', 'rekap_detail_napi', 'rekap_detail_tahanan',
                    'rekap_petugas', 'rekap_pengunjung', 'rekap_wbp_dikunjungi',
                    'rekap_wbp_vidcall', 'rekap_wbp_wartel', 'rekap_barang_titipan'
                ];

                $aggregated = [
                    'id' => 'kanwil',
                    'upt' => ['name' => 'KUMULATIF KANWIL (Semua UPT)'],
                    'tanggal' => $tanggal,
                ];

                foreach ($rekapFields as $field) {
                    $aggregated[$field] = [];
                    $aggregated['breakdown_' . $field] = [];
                }

                foreach ($dataRegistrasi as $item) {
                    foreach ($rekapFields as $field) {
                        $dataArray = $item->$field ?? [];
                        if (is_array($dataArray)) {
                            foreach ($dataArray as $key => $val) {
                                // 🛠️ Logika Rekap Spesifik WNA
                                if ($field === 'rekap_umum' && $key === 'detail_wna') {
                                    if (!isset($aggregated[$field][$key])) $aggregated[$field][$key] = [];
                                    if (is_array($val)) {
                                        foreach ($val as $wna) {
                                            $wna['upt_name'] = $item->upt->name ?? 'UPT Tidak Diketahui';
                                            $aggregated[$field][$key][] = $wna;
                                        }
                                    }
                                } 
                                // 🛠️ Logika Rekap Spesifik Overstaying (Filter text Nihil & jadikan Array)
                                elseif ($field === 'rekap_overstaying' && isset($val['jumlah'])) {
                                    if (!isset($aggregated[$field][$key])) {
                                        $aggregated[$field][$key] = ['jumlah' => 0, 'detail_keterangan' => []];
                                    }
                                    $aggregated[$field][$key]['jumlah'] += (int)$val['jumlah'];
                                    
                                    $ket = trim($val['keterangan'] ?? '');
                                    // Hanya masukkan keterangan jika isinya BUKAN Nihil, 0, atau kosong
                                    if (!empty($ket) && !in_array(strtolower($ket), ['nihil', '-', '0', 'tidak ada', 'kosong'])) {
                                        $aggregated[$field][$key]['detail_keterangan'][] = [
                                            'upt' => $item->upt->name ?? 'UPT',
                                            'text' => $ket
                                        ];
                                    }
                                }
                                // Sum untuk Number biasa
                                elseif (is_numeric($val)) {
                                    $aggregated[$field][$key] = ($aggregated[$field][$key] ?? 0) + $val;
                                    
                                    if ($val > 0) {
                                        if (!isset($aggregated['breakdown_' . $field][$key])) {
                                            $aggregated['breakdown_' . $field][$key] = [];
                                        }
                                        $aggregated['breakdown_' . $field][$key][] = [
                                            'upt_name' => $item->upt->name ?? 'UPT Tidak Diketahui',
                                            'jumlah' => $val
                                        ];
                                    }
                                } else {
                                    $aggregated[$field][$key] = $val;
                                }
                            }
                        }
                    }
                }
                
                $dataDinamis = collect([(object) $aggregated]);
            } else {
                $dataDinamis = $dataRegistrasi;
            }
        } 
        elseif ($modul === 'residivis') { $dataDinamis = $dataResidivis; } 
        elseif ($modul === 'pemindahan') { $dataDinamis = $dataPemindahan; } 
        elseif ($modul === 'integrasi') { $dataDinamis = $dataIntegrasi; }

        if ($isUptUser) {
            $upts = Upt::where('id', $user->upt_id)->get();
        } else {
            $upts = Upt::where('is_active', true)->get();
        }

        $viewData = array_merge([
            // 🛠️ Sisipkan 'is_upt_user' ke dalam filters agar bisa dibaca Vue
            'filters' => [
                'tanggal' => $tanggal, 
                'upt_id' => $uptId ?? '', 
                'modul' => $modul,
                'is_upt_user' => $isUptUser 
            ],
            'upts' => $upts,
            'highlight' => $highlight,
            'data_dinamis' => $dataDinamis,
            'summary_data' => ['kepribadian' => $dataKepribadian, 'kemandirian' => $dataKemandirian]
        ], $this->getMasterData());

        return Inertia::render('admin/pembinaan/dashboard/Index', $viewData);
    }

    public function rekapIntegrasi(Request $request)
    {
        $user = auth()->user();
        $isUptUser = !empty($user->upt_id);

        // Ambil parameter tanggal dari query string
        $dari   = $request->dari   ?? date('Y-m-01'); // default: awal bulan ini
        $sampai = $request->sampai ?? date('Y-m-d');  // default: hari ini

        // Ambil semua jenis integrasi sebagai kolom
        $jenisIntegrasis = \App\Models\JenisIntegrasi::orderBy('id')->get();

        // Query data registrasi dalam rentang tanggal
        $query = \App\Models\DataRegistrasi::with('upt')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->orderBy('upt_id')
            ->orderBy('tanggal');

        // Batasi per-UPT jika user adalah orang UPT
        if ($isUptUser) {
            $query->where('upt_id', $user->upt_id);
        }

        $dataRegistrasi = $query->get();

        // Kelompokkan data per-UPT, lalu per-tanggal
        $perUpt = [];

        // Inisialisasi semua UPT yang relevan agar UPT tanpa data tetap tampil
        if ($isUptUser) {
            $allUpts = \App\Models\Upt::where('id', $user->upt_id)->get();
        } else {
            $allUpts = \App\Models\Upt::where('is_active', true)->orderBy('name')->get();
        }

        foreach ($allUpts as $upt) {
            $perUpt[$upt->id] = [
                'upt_id'   => $upt->id,
                'upt_name' => $upt->name,
                'rows'     => [],
                'subtotal' => array_fill_keys($jenisIntegrasis->pluck('id')->toArray(), 0),
            ];
        }

        // Isi baris data harian
        foreach ($dataRegistrasi as $item) {
            $uptId = $item->upt_id;

            // Jika UPT belum ada di array (edge case), skip
            if (!isset($perUpt[$uptId])) continue;

            $rekapIntegrasi = $item->rekap_integrasi ?? [];
            $rowRekap = [];

            foreach ($jenisIntegrasis as $jenis) {
                $val = isset($rekapIntegrasi[$jenis->id]) ? (int) $rekapIntegrasi[$jenis->id] : 0;
                $rowRekap[$jenis->id] = $val;
                $perUpt[$uptId]['subtotal'][$jenis->id] += $val;
            }

            $perUpt[$uptId]['rows'][] = [
                'tanggal' => $item->tanggal,
                'rekap'   => $rowRekap,
            ];
        }

        // Hitung grand total dari subtotal semua UPT
        $grandTotal = array_fill_keys($jenisIntegrasis->pluck('id')->toArray(), 0);
        foreach ($perUpt as $uptData) {
            foreach ($jenisIntegrasis as $jenis) {
                $grandTotal[$jenis->id] += $uptData['subtotal'][$jenis->id] ?? 0;
            }
        }

        return Inertia::render('admin/pembinaan/dashboard/RekapIntegrasi', [
            'dari'            => $dari,
            'sampai'          => $sampai,
            'jenis_integrasis' => $jenisIntegrasis,
            'per_upt'         => array_values($perUpt),
            'grand_total'     => $grandTotal,
        ]);
    }

    public function exportRekapIntegrasiExcel(Request $request)
    {
        $user      = auth()->user();
        $isUptUser = !empty($user->upt_id);

        $dari   = $request->dari   ?? date('Y-m-01');
        $sampai = $request->sampai ?? date('Y-m-d');

        $jenisIntegrasis = \App\Models\JenisIntegrasi::orderBy('id')->get();

        $query = \App\Models\DataRegistrasi::with('upt')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->orderBy('upt_id')
            ->orderBy('tanggal');

        if ($isUptUser) {
            $query->where('upt_id', $user->upt_id);
        }

        $dataRegistrasi = $query->get();

        if ($isUptUser) {
            $allUpts = Upt::where('id', $user->upt_id)->get();
        } else {
            $allUpts = Upt::where('is_active', true)->orderBy('name')->get();
        }

        $perUpt = [];
        foreach ($allUpts as $upt) {
            $perUpt[$upt->id] = [
                'upt_id'   => $upt->id,
                'upt_name' => $upt->name,
                'rows'     => [],
                'subtotal' => array_fill_keys($jenisIntegrasis->pluck('id')->toArray(), 0),
            ];
        }

        foreach ($dataRegistrasi as $item) {
            $uptId = $item->upt_id;
            if (!isset($perUpt[$uptId])) continue;

            $rekapIntegrasi = $item->rekap_integrasi ?? [];
            $rowRekap = [];
            foreach ($jenisIntegrasis as $jenis) {
                $val = isset($rekapIntegrasi[$jenis->id]) ? (int) $rekapIntegrasi[$jenis->id] : 0;
                $rowRekap[$jenis->id] = $val;
                $perUpt[$uptId]['subtotal'][$jenis->id] += $val;
            }
            $perUpt[$uptId]['rows'][] = [
                'tanggal' => $item->tanggal,
                'rekap'   => $rowRekap,
            ];
        }

        $grandTotal = array_fill_keys($jenisIntegrasis->pluck('id')->toArray(), 0);
        foreach ($perUpt as $uptData) {
            foreach ($jenisIntegrasis as $jenis) {
                $grandTotal[$jenis->id] += $uptData['subtotal'][$jenis->id] ?? 0;
            }
        }

        // Konversi ke array biasa agar kompatibel dengan Export class
        $jenisArray = $jenisIntegrasis->map(fn($j) => ['id' => $j->id, 'nama_integrasi' => $j->nama_integrasi])->toArray();
        $perUptArray = array_values($perUpt);

        return (new RekapIntegrasiExport($dari, $sampai, $jenisArray, $perUptArray, $grandTotal))->download();
    }
}