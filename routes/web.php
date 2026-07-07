<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\DataAkunController;
use App\Http\Controllers\Admin\KanwilController;
use App\Http\Controllers\Admin\UptController;
use App\Http\Controllers\Admin\JenisPidanaController;
use App\Http\Controllers\Admin\JenisKepribadianController;
use App\Http\Controllers\Admin\JenisKemandirianController;
use App\Http\Controllers\Admin\JenisRemisiController;
use App\Http\Controllers\Admin\JenisPemindahanController;
use App\Http\Controllers\Admin\JenisPengawalanController;
use App\Http\Controllers\Admin\JenisPersonelController;
use App\Http\Controllers\Admin\JenisKlasifikasiController;
use App\Http\Controllers\Admin\JenisPendidikanController;
use App\Http\Controllers\Admin\JenisUsulanController;
use App\Http\Controllers\Admin\JenisStrukturalController;
use App\Http\Controllers\Admin\JenisGolonganController;
use App\Http\Controllers\Admin\JenisBebanController;
use App\Http\Controllers\Admin\JenisStaffController;
use App\Http\Controllers\Admin\JenisIntegrasiController;
use App\Http\Controllers\Admin\JenisAgamaController;
use App\Http\Controllers\Admin\JenisPengeluaranController;
use App\Http\Controllers\Admin\RegistrasiUmumController;
use App\Http\Controllers\Admin\RegistrasiIntegrasiController;
use App\Http\Controllers\Admin\RegistrasiPidsusController;
use App\Http\Controllers\Admin\RegistrasiPidumController;
use App\Http\Controllers\Admin\RegistrasiOverstayingController;
use App\Http\Controllers\Admin\RegistrasiIdentitasController;
use App\Http\Controllers\Admin\RegistrasiPetugasController;
use App\Http\Controllers\Admin\RegistrasiPengunjungController;
use App\Http\Controllers\Admin\RegistrasiWbpDikunjungiController;
use App\Http\Controllers\Admin\RegistrasiWbpVidcallController;
use App\Http\Controllers\Admin\RegistrasiWbpWartelController;
use App\Http\Controllers\Admin\RegistrasiBarangTitipanController;
use App\Http\Controllers\Admin\RegistrasiPendidikanController;
use App\Http\Controllers\Admin\RegistrasiDetailNapiController;
use App\Http\Controllers\Admin\RegistrasiDetailTahananController;
use App\Http\Controllers\Admin\DataRegistrasiController;
use App\Http\Controllers\Admin\DataResidivisController;
use App\Http\Controllers\Admin\DataPembinaanKepribadianController;
use App\Http\Controllers\Admin\DataPembinaanKemandirianController;
use App\Http\Controllers\Admin\DataIntegrasiTppController;
use App\Http\Controllers\Admin\DataIntegrasiController;
use App\Http\Controllers\Admin\DataRemisiController;
use App\Http\Controllers\Admin\DataPemindahanController;
use App\Http\Controllers\Admin\DataPerawatanHarianController;
use App\Http\Controllers\Admin\DataPerawatanBulananController;
use App\Http\Controllers\Admin\DataPenghuniRiilController;
use App\Http\Controllers\Admin\DataKontrolKelilingController;
use App\Http\Controllers\Admin\DataReguPengamananController;
use App\Http\Controllers\Admin\DataGangguanKamtibController;
use App\Http\Controllers\Admin\DataSarprasKeamananController;
use App\Http\Controllers\Admin\DataSatgasPengamananController;
use App\Http\Controllers\Admin\DataInformasiIntelejenController;
use App\Http\Controllers\Admin\DataPelanggaranTatibController;
use App\Http\Controllers\Admin\DataPengawalanPengamananController;
use App\Http\Controllers\Admin\DataHarianSdmController;
use App\Http\Controllers\Admin\DataBulananSdmController;
use App\Http\Controllers\Admin\DataSdmBulananController;
use App\Http\Controllers\Admin\DataPublikasiSdmController;
use App\Http\Controllers\Admin\DataRealisasiAnggaranController;
use App\Http\Controllers\Admin\DataMouPksController;
use App\Http\Controllers\Admin\DataBookingRapatController;
use App\Http\Controllers\FileStreamController;
use App\Http\Controllers\Admin\DashboardPembinaanController;
use App\Http\Controllers\Admin\DashboardTuController;



Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});
Route::get('/view-file', [FileStreamController::class, 'show'])->name('view.file');

Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    //Controller Data Master
    Route::resource('roles', RoleController::class);
    Route::resource('data-akun', DataAkunController::class)->names('data-akun');
    Route::resource('kanwils', KanwilController::class);
    Route::resource('upts', UptController::class);
    Route::resource('jenis-pidanas', JenisPidanaController::class);
    Route::resource('jenis-kepribadians', JenisKepribadianController::class);
    Route::resource('jenis-kemandirians', JenisKemandirianController::class);
    Route::resource('jenis-remisis', JenisRemisiController::class);
    Route::resource('jenis-pemindahans', JenisPemindahanController::class);
    Route::resource('jenis-pengawalans', JenisPengawalanController::class);
    Route::resource('jenis-personels', JenisPersonelController::class);
    Route::resource('jenis-klasifikasis', JenisKlasifikasiController::class);
    Route::resource('jenis-pendidikans', JenisPendidikanController::class);
    Route::resource('jenis-usulans', JenisUsulanController::class);
    Route::resource('jenis-strukturals', JenisStrukturalController::class);
    Route::resource('jenis-golongans', JenisGolonganController::class);
    Route::resource('jenis-bebans', JenisBebanController::class);
    Route::resource('jenis-staffs', JenisStaffController::class);
    Route::resource('jenis-integrasis', JenisIntegrasiController::class);
    Route::resource('jenis-agamas', JenisAgamaController::class);
    Route::resource('jenis-pengeluarans', JenisPengeluaranController::class);
    Route::resource('data-registrasis', DataRegistrasiController::class);
    Route::resource('data-residivises', DataResidivisController::class);
    Route::resource('data-pembinaan-kepribadians', DataPembinaanKepribadianController::class);
    Route::resource('data-pembinaan-kemandirians', DataPembinaanKemandirianController::class);
    Route::resource('data-integrasi-tpps', DataIntegrasiTppController::class);
    Route::resource('data-integrasis', DataIntegrasiController::class);
    Route::resource('data-remisis', DataRemisiController::class);
    Route::resource('data-pemindahans', DataPemindahanController::class);
    Route::resource('data-perawatan-harians', DataPerawatanHarianController::class);
    Route::resource('data-perawatan-bulanans', DataPerawatanBulananController::class);
    Route::resource('data-penghuni-riils', DataPenghuniRiilController::class);
    Route::resource('data-kontrol-kelilings', DataKontrolKelilingController::class);
    Route::resource('data-regu-pengamanans', DataReguPengamananController::class);
    Route::resource('data-gangguan-kamtibs', DataGangguanKamtibController::class);
    Route::resource('data-sarpras-keamanans', DataSarprasKeamananController::class);
    Route::resource('data-satgas-pengamanans', DataSatgasPengamananController::class);
    Route::resource('data-informasi-intelejens', DataInformasiIntelejenController::class);
    Route::resource('data-pelanggaran-tatibs', DataPelanggaranTatibController::class);
    Route::resource('data-pengawalan-pengamanans', DataPengawalanPengamananController::class);
    Route::resource('data-harian-sdms', DataHarianSdmController::class);
    Route::resource('data-bulanan-sdms', DataBulananSdmController::class); 
    Route::resource('data-sdm-bulanans', DataSdmBulananController::class);
    Route::resource('data-publikasi-sdms', DataPublikasiSdmController::class);
    Route::resource('data-realisasi-anggarans', DataRealisasiAnggaranController::class);
    Route::resource('data-mou-pks', DataMouPksController::class);
    Route::resource('data-booking-rapats', DataBookingRapatController::class);

    //Controller Data Master Registrasi
    Route::resource('registrasi-umums', RegistrasiUmumController::class);
    Route::resource('registrasi-integrasis', RegistrasiIntegrasiController::class);
    Route::resource('registrasi-pidsuses', RegistrasiPidsusController::class);
    Route::resource('registrasi-pidums', RegistrasiPidumController::class);
    Route::resource('registrasi-overstayings', RegistrasiOverstayingController::class);
    Route::resource('registrasi-identitases', RegistrasiIdentitasController::class);
    Route::resource('registrasi-petugases', RegistrasiPetugasController::class);
    Route::resource('registrasi-pengunjungs', RegistrasiPengunjungController::class);
    Route::resource('registrasi-wbp-dikunjungis', RegistrasiWbpDikunjungiController::class);
    Route::resource('registrasi-wbp-vidcalls', RegistrasiWbpVidcallController::class);
    Route::resource('registrasi-wbp-wartels', RegistrasiWbpWartelController::class);
    Route::resource('registrasi-barang-titipans', RegistrasiBarangTitipanController::class);
    Route::resource('registrasi-pendidikans', RegistrasiPendidikanController::class);
    Route::resource('registrasi-detail-napis', RegistrasiDetailNapiController::class);
    Route::resource('registrasi-detail-tahanans', RegistrasiDetailTahananController::class);

    Route::get('/pembinaan/dashboard', [DashboardPembinaanController::class, 'index'])->name('pembinaan.dashboard');
    Route::get('/pembinaan/rekap-integrasi', [DashboardPembinaanController::class, 'rekapIntegrasi'])->name('pembinaan.rekap-integrasi');
    Route::get('/pembinaan/rekap-integrasi/export-excel', [DashboardPembinaanController::class, 'exportRekapIntegrasiExcel'])->name('pembinaan.rekap-integrasi.excel');
    Route::get('/tudanumum/dashboard', [DashboardTuController::class, 'index'])->name('dashboard.tu');

});

require __DIR__ . '/settings.php';
