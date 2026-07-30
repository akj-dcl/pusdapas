<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import { debounce } from 'lodash'

// ── Modal Cetak Rekap Integrasi ──────────────────────────────────
const showModalCetak = ref(false)
const today = new Date()
const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0]
const cetakDari   = ref(firstDayOfMonth)
const cetakSampai = ref(today.toISOString().split('T')[0])

function openModalCetak() { showModalCetak.value = true }
function closeModalCetak() { showModalCetak.value = false }

function bukaCetak() {
    const url = `/admin/pembinaan/rekap-integrasi?dari=${cetakDari.value}&sampai=${cetakSampai.value}`
    window.open(url, '_blank')
    closeModalCetak()
}

function bukaExcel() {
    const url = `/admin/pembinaan/rekap-integrasi/export-excel?dari=${cetakDari.value}&sampai=${cetakSampai.value}`
    window.open(url, '_blank')
    closeModalCetak()
}

const targetBreakdownNames = ['anak', 'wanita', 'anak bawaan', 'ibu hamil', 'lansia', 'pidana seumur hidup', 'pidana mati', 'mt', 'sh'];

function isTargetBreakdown(name: string) {
    return targetBreakdownNames.includes(name.toLowerCase().trim());
}

const showModalBreakdown = ref(false);
const breakdownTitle = ref('');
const breakdownData = ref<any[]>([]);

function openBreakdown(name: string, data: any[]) {
    breakdownTitle.value = name;
    breakdownData.value = data || [];
    showModalBreakdown.value = true;
}
function closeBreakdown() {
    showModalBreakdown.value = false;
}


const props = defineProps<{
    filters: any,
    upts: any[],
    highlight: any,
    data_dinamis: any[],
    summary_data: any,
    umums: any[], pidsuses: any[], pidums: any[], overstayings: any[], 
    integrasis: any[], identitases: any[], agamas: any[], pengeluarans: any[],
    pendidikans: any[], detail_napis: any[], detail_tahanans: any[], 
    petugases: any[], pengunjungs: any[], wbp_dikunjungis: any[], 
    wbp_vidcalls: any[], wbp_wartels: any[], barang_titipans: any[]
}>()

const tanggal = ref(props.filters.tanggal)
const upt_id = ref(props.filters.upt_id)
const modul = ref(props.filters.modul)

watch([tanggal, upt_id, modul], debounce(([newTanggal, newUpt, newModul]) => {
    router.get(window.location.pathname, {
        tanggal: newTanggal,
        upt_id: newUpt,
        modul: newModul
    }, { preserveState: true, preserveScroll: true })
}, 300))

function findName(array: any[], id: string | number, keyName: string) {
  const item = array?.find(i => String(i.id) === String(id));
  return item ? item[keyName] : '-';
}

function objTotalExclude(obj: any, excludeKey: any) {
    if (!obj) return 0;
    return Object.entries(obj).reduce((sum, [key, val]) => {
        if (String(key) === String(excludeKey)) return sum;
        if (typeof val === 'number') return sum + val;
        if (typeof val === 'string' && !isNaN(Number(val))) return sum + Number(val);
        return sum;
    }, 0);
}
function objTotal(obj: any) { return objTotalExclude(obj, null); }

const idTotalIsi = computed(() => props.umums?.find(i => i.nama_registrasiumum.toLowerCase().includes('total isi') || i.nama_registrasiumum.toLowerCase().includes('total_isi'))?.id);
const idKapasitas = computed(() => props.umums?.find(i => i.nama_registrasiumum.toLowerCase().includes('kapasitas'))?.id);
const idPidsusResidivis = computed(() => props.pidsuses?.find(i => i.nama_registrasipidsus.toLowerCase().includes('residivis'))?.id);
const idPidumResidivis = computed(() => props.pidums?.find(i => i.nama_registrasipidum.toLowerCase().includes('residivis'))?.id);

const idAdaNik = computed(() => props.identitases?.find(i => i.nama_registrasiidentitas.toLowerCase().includes('ada nik') && !i.nama_registrasiidentitas.toLowerCase().includes('tidak'))?.id);
const idTidakAdaNik = computed(() => props.identitases?.find(i => i.nama_registrasiidentitas.toLowerCase().includes('tidak ada nik'))?.id);
const idAdaKtp = computed(() => props.identitases?.find(i => i.nama_registrasiidentitas.toLowerCase().includes('ada ktp') && !i.nama_registrasiidentitas.toLowerCase().includes('tidak'))?.id);
const idTidakAdaKtp = computed(() => props.identitases?.find(i => i.nama_registrasiidentitas.toLowerCase().includes('tidak ada ktp'))?.id);

function getTotalIsi(d: any) { return d.rekap_umum && idTotalIsi.value ? Number(d.rekap_umum[idTotalIsi.value] || 0) : 0; }
function getTotalPidsus(d: any) { return objTotalExclude(d.rekap_pidsus, idPidsusResidivis.value); }
function getTotalPidum(d: any) { return objTotalExclude(d.rekap_pidum, idPidumResidivis.value); }
function getTotalPidsusPidum(d: any) { return getTotalPidsus(d) + getTotalPidum(d); }
function getTotalNik(d: any) { return (Number(d.rekap_identitas?.[idAdaNik.value]) || 0) + (Number(d.rekap_identitas?.[idTidakAdaNik.value]) || 0); }
function getTotalKtp(d: any) { return (Number(d.rekap_identitas?.[idAdaKtp.value]) || 0) + (Number(d.rekap_identitas?.[idTidakAdaKtp.value]) || 0); }

function getOvercrowdedPersen(d: any) {
    if (!d.rekap_umum) return '0%';
    const total = Number(d.rekap_umum[idTotalIsi.value] || 0);
    const kapasitas = Number(d.rekap_umum[idKapasitas.value] || 0);
    if (kapasitas === 0) return '0%';
    const percent = ((total - kapasitas) / kapasitas) * 100;
    return percent.toFixed(2) + '%';
}
</script>

<template>
  <Head title="Dashboard Eksekutif Pembinaan" />
  <AppLayout>
    <div class="p-4 md:p-6 space-y-6 bg-slate-50/50 dark:bg-slate-900/50 min-h-screen text-foreground">
      
      <div class="bg-card p-5 rounded-2xl shadow-sm border flex flex-col lg:flex-row gap-4 lg:items-center justify-between">
          <div>
              <h1 class="text-2xl font-bold tracking-tight text-primary">Dashboard Eksekutif Pembinaan</h1>
              <p class="text-sm text-muted-foreground">Analisis data harian warga binaan pemasyarakatan.</p>
          </div>
          <div class="flex flex-wrap md:flex-nowrap gap-3 w-full lg:w-auto items-end">
              <div class="flex-1 md:w-auto">
                  <label class="text-[10px] uppercase font-bold text-muted-foreground block mb-1">Pilih Tanggal</label>
                  <input v-model="tanggal" type="date" class="w-full border rounded-lg px-3 py-2 text-sm bg-background shadow-sm" />
              </div>
              <div class="flex-1 md:w-auto">
                  <label class="text-[10px] uppercase font-bold text-muted-foreground block mb-1">Filter Wilayah / UPT</label>
                  <select 
                      v-model="upt_id" 
                      :disabled="filters.is_upt_user"
                      class="w-full border rounded-lg px-3 py-2 text-sm font-semibold shadow-sm"
                      :class="filters.is_upt_user ? 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 cursor-not-allowed' : 'bg-background'"
                  >
                      <option value="" v-if="!filters.is_upt_user">🏢 KANWIL (Semua UPT)</option>
                      <option v-for="u in upts" :value="String(u.id)">{{ u.name }}</option>
                  </select>
              </div>
              <div class="flex-1 md:w-auto">
                  <label class="text-[10px] uppercase font-bold text-primary block mb-1">Detail Modul</label>
                  <select v-model="modul" class="w-full border-0 rounded-lg px-3 py-2 text-sm bg-primary text-primary-foreground font-bold shadow-md cursor-pointer hover:bg-primary/90 transition-colors">
                      <option value="ringkasan">Ringkasan Total</option>
                      <option value="registrasi">Data Registrasi</option>
                      <option value="residivis">Data Residivis</option>
                      <option value="pemindahan">Data Pemindahan</option>
                      <option value="integrasi">Data Integrasi</option>
                  </select>
              </div>
              <!-- Tombol Cetak Rekap Integrasi -->
              <div class="flex-shrink-0">
                  <label class="text-[10px] uppercase font-bold text-emerald-700 dark:text-emerald-400 block mb-1">Cetak</label>
                  <button
                      @click="openModalCetak"
                      class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-sm font-bold px-4 py-2 rounded-lg shadow-md transition-all duration-150 whitespace-nowrap"
                  >
                      <span>🖨️</span>
                      <span>Rekap Integrasi</span>
                  </button>
              </div>
          </div>
      </div>

      <!-- Modal Cetak Rekap Integrasi -->
      <Teleport to="body">
          <div
              v-if="showModalCetak"
              class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
              @click.self="closeModalCetak"
          >
              <div class="bg-card text-card-foreground border border-border rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 animate-in fade-in zoom-in-95 duration-200">
                  <!-- Header Modal -->
                  <div class="flex items-center justify-between mb-5">
                      <div>
                          <h3 class="text-lg font-black text-slate-800 dark:text-slate-200">🖨️ Cetak Rekap Integrasi</h3>
                          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Data diambil per-UPT, detail per tanggal</p>
                      </div>
                      <button @click="closeModalCetak" class="text-slate-400 hover:text-slate-600 dark:text-slate-400 text-xl leading-none transition-colors">&times;</button>
                  </div>

                  <!-- Form Tanggal -->
                  <div class="space-y-4">
                      <div>
                          <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide block mb-1.5">Dari Tanggal</label>
                          <input
                              v-model="cetakDari"
                              type="date"
                              class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent transition"
                          />
                      </div>
                      <div>
                          <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide block mb-1.5">Sampai Tanggal</label>
                          <input
                              v-model="cetakSampai"
                              type="date"
                              class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent transition"
                          />
                      </div>
                  </div>

                  <!-- Preview Info -->
                  <div class="mt-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-100 dark:border-emerald-900/50 rounded-xl px-4 py-3 text-xs text-emerald-800 dark:text-emerald-300">
                      <p class="font-bold mb-1">📋 Yang akan dicetak:</p>
                      <ul class="space-y-0.5 text-emerald-700 dark:text-emerald-400">
                          <li>• Tabel rekap integrasi per-UPT</li>
                          <li>• Detail baris per tanggal yang ada data</li>
                          <li>• Subtotal per-UPT &amp; Grand Total Kanwil</li>
                      </ul>
                  </div>

                  <!-- Tombol Aksi -->
                  <div class="flex flex-col gap-2 mt-6">
                      <div class="grid grid-cols-2 gap-2">
                          <button
                              @click="bukaCetak"
                              :disabled="!cetakDari || !cetakSampai"
                              class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-2.5 rounded-xl text-sm transition flex items-center justify-center gap-2"
                          >
                              <span>🖨️</span> Cetak PDF
                          </button>
                          <button
                              @click="bukaExcel"
                              :disabled="!cetakDari || !cetakSampai"
                              class="bg-green-700 hover:bg-green-800 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-2.5 rounded-xl text-sm transition flex items-center justify-center gap-2"
                          >
                              <span>📊</span> Export Excel
                          </button>
                      </div>
                      <button
                          @click="closeModalCetak"
                          class="w-full border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:bg-slate-800/80 dark:hover:bg-slate-800 font-semibold py-2 rounded-xl text-sm transition"
                      >Batal</button>
                  </div>
              </div>
          </div>
      </Teleport>

      <!-- Modal Breakdown UPT -->
      <Teleport to="body">
          <div
              v-if="showModalBreakdown"
              class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
              @click.self="closeBreakdown"
          >
              <div class="bg-card text-card-foreground border border-border rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 animate-in fade-in zoom-in-95 duration-200">
                  <div class="flex items-center justify-between mb-5 border-b pb-3">
                      <div>
                          <h3 class="text-lg font-black text-slate-800 dark:text-slate-200">🔍 Detail {{ breakdownTitle }}</h3>
                          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Rincian sebaran per-UPT</p>
                      </div>
                      <button @click="closeBreakdown" class="text-slate-400 hover:text-slate-600 dark:text-slate-400 text-xl leading-none transition-colors">&times;</button>
                  </div>
                  
                  <div class="max-h-[60vh] overflow-y-auto space-y-2 pr-1 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-thumb]:bg-slate-200 [&::-webkit-scrollbar-thumb]:rounded-full">
                      <div v-if="breakdownData.length === 0" class="text-center text-sm text-slate-500 dark:text-slate-400 py-4 italic">Tidak ada data.</div>
                      <div v-else v-for="(item, idx) in breakdownData" :key="idx" class="flex justify-between items-center bg-slate-50 dark:bg-slate-800/80 border border-slate-100 dark:border-slate-700 p-3 rounded-xl shadow-sm">
                          <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ item.upt_name }}</span>
                          <span class="text-xs font-black bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-400 px-2 py-1 rounded-md">{{ item.jumlah }} Orang</span>
                      </div>
                  </div>
                  
                  <div class="mt-5 pt-3 border-t">
                      <button @click="closeBreakdown" class="w-full bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold py-2.5 rounded-xl text-sm transition">Tutup</button>
                  </div>
              </div>
          </div>
      </Teleport>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="bg-card p-5 rounded-2xl shadow-sm border border-l-4 border-l-red-500 hover:shadow-md transition-shadow">
              <div class="flex justify-between items-start">
                  <p class="text-xs text-muted-foreground font-bold uppercase">Residivis Baru</p>
                  <span class="bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-400 text-[10px] px-2 py-0.5 rounded-full font-bold">Hari Ini</span>
              </div>
              <h2 class="text-3xl font-black text-red-600 dark:text-red-400 mt-2">{{ highlight.total_residivis }} <span class="text-sm font-medium text-muted-foreground">WBP</span></h2>
          </div>
          <div class="bg-card p-5 rounded-2xl shadow-sm border border-l-4 border-l-emerald-500 hover:shadow-md transition-shadow">
              <div class="flex justify-between items-start">
                  <p class="text-xs text-muted-foreground font-bold uppercase">Kegiatan Pembinaan</p>
                  <span class="bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-400 text-[10px] px-2 py-0.5 rounded-full font-bold">Total Giat</span>
              </div>
              <h2 class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-2">{{ highlight.kegiatan_kepribadian + highlight.kegiatan_kemandirian }} <span class="text-sm font-medium text-muted-foreground">Giat</span></h2>
              <p class="text-[10px] text-muted-foreground mt-1 font-medium">Diikuti {{ highlight.total_peserta_kepribadian + highlight.total_peserta_kemandirian }} WBP</p>
          </div>
          <div class="bg-card p-5 rounded-2xl shadow-sm border border-l-4 border-l-blue-500 hover:shadow-md transition-shadow">
              <div class="flex justify-between items-start">
                  <p class="text-xs text-muted-foreground font-bold uppercase">WBP Integrasi</p>
                  <span class="bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-400 text-[10px] px-2 py-0.5 rounded-full font-bold">Disetujui</span>
              </div>
              <h2 class="text-3xl font-black text-blue-600 dark:text-blue-400 mt-2">{{ highlight.total_integrasi }} <span class="text-sm font-medium text-muted-foreground">Orang</span></h2>
          </div>
          <div class="bg-card p-5 rounded-2xl shadow-sm border border-l-4 border-l-orange-500 hover:shadow-md transition-shadow">
              <div class="flex justify-between items-start">
                  <p class="text-xs text-muted-foreground font-bold uppercase">Lalu Lintas Pemindahan</p>
                  <span class="bg-orange-100 dark:bg-orange-900/50 text-orange-700 dark:text-orange-400 text-[10px] px-2 py-0.5 rounded-full font-bold">Berkas</span>
              </div>
              <h2 class="text-3xl font-black text-orange-600 dark:text-orange-400 mt-2">{{ highlight.total_pemindahan }} <span class="text-sm font-medium text-muted-foreground">Rute</span></h2>
          </div>
      </div>

      <div class="bg-card p-6 rounded-2xl shadow-sm border min-h-[400px]">
          
          <div v-if="modul === 'ringkasan'" class="space-y-6 animate-in fade-in duration-500">
              <div class="flex items-center gap-2 border-b pb-2"><h2 class="text-lg font-bold text-primary">Rekapitulasi Pelaksanaan Hari Ini</h2></div>
              <div class="grid md:grid-cols-2 gap-6">
                  <div class="bg-blue-50/50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-100 dark:border-blue-900/50">
                      <h3 class="font-bold text-blue-800 dark:text-blue-300 mb-3 flex items-center gap-2">📘 Giat Kepribadian ({{ highlight.kegiatan_kepribadian }})</h3>
                      <div v-if="summary_data.kepribadian.length" class="space-y-2">
                          <div v-for="k in summary_data.kepribadian" :key="k.id" class="bg-card p-3 rounded-lg border text-sm flex justify-between items-center shadow-sm">
                              <div><p class="font-bold text-slate-800 dark:text-slate-200">{{ k.nama_kegiatan }}</p><p class="text-[10px] text-muted-foreground">Pemateri: {{ k.pemateri }}</p></div>
                              <span class="bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-400 px-2 py-1 rounded text-xs font-bold">{{ k.jumlah_peserta }} Peserta</span>
                          </div>
                      </div>
                      <p v-else class="text-xs text-muted-foreground italic text-center py-4">Nihil kegiatan kepribadian hari ini.</p>
                  </div>
                  <div class="bg-emerald-50/50 dark:bg-emerald-900/20 rounded-xl p-4 border border-emerald-100 dark:border-emerald-900/50">
                      <h3 class="font-bold text-emerald-800 dark:text-emerald-300 mb-3 flex items-center gap-2">🛠️ Giat Kemandirian ({{ highlight.kegiatan_kemandirian }})</h3>
                      <div v-if="summary_data.kemandirian.length" class="space-y-2">
                          <div v-for="k in summary_data.kemandirian" :key="k.id" class="bg-card p-3 rounded-lg border text-sm flex justify-between items-center shadow-sm">
                              <div><p class="font-bold text-slate-800 dark:text-slate-200">{{ k.nama_kegiatan }}</p></div>
                              <span class="bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-400 px-2 py-1 rounded text-xs font-bold">{{ k.jumlah_peserta }} Peserta</span>
                          </div>
                      </div>
                      <p v-else class="text-xs text-muted-foreground italic text-center py-4">Nihil kegiatan kemandirian hari ini.</p>
                  </div>
              </div>
          </div>

          <div v-else-if="modul === 'registrasi'" class="animate-in fade-in slide-in-from-bottom-2 duration-300">
              <h2 class="text-lg font-bold mb-4 flex items-center gap-2">👥 Rincian Data Registrasi Harian</h2>
              
              <div v-if="!data_dinamis.length" class="text-center py-8 text-muted-foreground italic bg-card rounded-xl border">Nihil data registrasi pada tanggal ini.</div>

              <div v-for="(d, index) in data_dinamis" :key="index" class="mb-8 last:mb-0">
                  <div class="bg-slate-800 text-slate-100 px-4 py-2 rounded-t-xl font-bold flex items-center gap-2">{{ d.upt?.name || 'KUMULATIF KANWIL (Semua UPT)' }}</div>
                  
                  <div class="bg-card border border-t-0 rounded-b-xl p-5 shadow-sm space-y-6">
                      <div>
                          <h3 class="text-sm font-black text-primary uppercase tracking-wide border-b pb-2 mb-4">A. Analisis Komposisi WBP</h3>
                          <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                              
                              <div class="rounded-lg bg-muted/30 p-3 border flex flex-col justify-between">
                                  <div>
                                      <h4 class="font-bold text-xs text-primary mb-2 border-b pb-1">Data Umum</h4>
                                      <ul class="space-y-1">
                                          <li v-for="(v, k) in d.rekap_umum" :key="k" class="flex justify-between items-center text-xs border-b border-dashed border-slate-100 dark:border-slate-700 last:border-0 py-1">
                                              <template v-if="k !== 'detail_wna'">
                                                  <span class="text-muted-foreground">{{ findName(umums, k, 'nama_registrasiumum') }}</span>
                                                  <span v-if="findName(umums, k, 'nama_registrasiumum').toLowerCase().includes('overcrowded')" class="font-bold text-red-600 dark:text-red-400">{{ getOvercrowdedPersen(d) }}</span>
                                                  <span v-else class="font-bold flex items-center gap-2">
                                                      {{ v }}
                                                      <button v-if="(!filters.upt_id && !filters.is_upt_user) && v > 0 && isTargetBreakdown(findName(umums, k, 'nama_registrasiumum'))" 
                                                              @click="openBreakdown(findName(umums, k, 'nama_registrasiumum'), d.breakdown_rekap_umum?.[k])" 
                                                              class="text-[9px] bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:bg-blue-900/50 border border-blue-200 dark:border-blue-800/50 px-1.5 py-0.5 rounded shadow-sm flex items-center gap-1 transition-colors">
                                                          <span>🔍</span> Detail
                                                      </button>
                                                  </span>
                                              </template>
                                          </li>
                                      </ul>
                                  </div>
                                  
                                  <div v-if="d.rekap_umum && d.rekap_umum['detail_wna'] && d.rekap_umum['detail_wna'].length > 0" class="mt-3 p-2 bg-blue-50/50 dark:bg-blue-900/20 rounded-lg border border-blue-100 dark:border-blue-900/50">
                                      <p class="text-[11px] font-bold text-blue-800 dark:text-blue-300 mb-2 border-b border-blue-200 dark:border-blue-800/50 pb-1 flex justify-between">
                                          <span>🌐 Rincian WNA</span>
                                          <span class="bg-blue-200 dark:bg-blue-800/50 text-blue-800 dark:text-blue-300 px-1.5 rounded-full">{{ d.rekap_umum['detail_wna'].length }} org</span>
                                      </p>
                                      <div class="max-h-48 overflow-y-auto space-y-1.5 pr-1 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-thumb]:bg-blue-200 dark:bg-blue-800/50 [&::-webkit-scrollbar-thumb]:rounded-full">
                                          <div v-for="(wna, i) in d.rekap_umum['detail_wna']" :key="i" class="flex flex-col text-[10px] bg-card px-2 py-1.5 rounded border shadow-sm">
                                              <div class="flex justify-between items-center">
                                                  <span class="font-bold text-slate-700 dark:text-slate-300">{{ wna.negara }}</span>
                                                  <span class="font-bold bg-blue-100 dark:bg-blue-900/50 px-1.5 py-0.5 rounded text-blue-800 dark:text-blue-300">{{ wna.status }}</span>
                                              </div>
                                              <div v-if="wna.upt_name" class="text-[9px] text-muted-foreground mt-1 pt-1 border-t border-slate-100 dark:border-slate-700 flex items-center gap-1">🏢 {{ wna.upt_name }}</div>
                                          </div>
                                      </div>
                                  </div>
                              </div>

                              <div class="rounded-lg bg-purple-50/40 dark:bg-purple-900/20 p-3 border border-purple-100 dark:border-purple-900/50 flex flex-col justify-between">
                                  <div>
                                      <h4 class="font-bold text-xs text-purple-700 dark:text-purple-400 mb-2 border-b pb-1">Tingkat Pendidikan</h4>
                                      <ul class="space-y-1"><li v-for="(v, k) in d.rekap_pendidikan" :key="k" class="flex justify-between text-xs"><span class="text-muted-foreground">{{ findName(pendidikans, k, 'nama_registrasipendidikan') }}</span><span class="font-bold text-purple-700 dark:text-purple-400">{{ v }}</span></li></ul>
                                  </div>
                                  <div class="mt-2 p-1.5 rounded border text-[10px] font-bold flex justify-between" :class="objTotal(d.rekap_pendidikan) === getTotalIsi(d) ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-100 dark:border-emerald-900/50 text-emerald-700 dark:text-emerald-400' : 'bg-red-50 dark:bg-red-900/30 border-red-100 dark:border-red-900/50 text-red-700 dark:text-red-400'">
                                      <span>Total: {{ objTotal(d.rekap_pendidikan) }}</span>
                                      <span v-if="objTotal(d.rekap_pendidikan) !== getTotalIsi(d)">⚠️ Selisih: {{ Math.abs(objTotal(d.rekap_pendidikan) - getTotalIsi(d)) }}</span><span v-else>✅ Sesuai</span>
                                  </div>
                              </div>

                              <div class="rounded-lg bg-emerald-50/40 dark:bg-emerald-900/20 p-3 border border-emerald-100 dark:border-emerald-900/50">
                                  <h4 class="font-bold text-xs text-emerald-700 dark:text-emerald-400 mb-2 border-b pb-1">Detail Rekap Napi</h4>
                                  <ul class="space-y-1">
                                      <li v-for="(v, k) in d.rekap_detail_napi" :key="k" class="flex justify-between items-center text-xs border-b border-dashed border-emerald-100/50 last:border-0 py-1">
                                          <span class="text-muted-foreground">
                                              {{ 
                                                  findName(detail_napis, k, 'nama_registrasidetailnapi') === 'MT' ? 'MT (Pidana Mati)' : 
                                                  (findName(detail_napis, k, 'nama_registrasidetailnapi') === 'SH' ? 'SH (Pidana Seumur Hidup)' : 
                                                  findName(detail_napis, k, 'nama_registrasidetailnapi')) 
                                              }}
                                          </span>
                                          <span class="font-bold flex items-center gap-2 text-emerald-700 dark:text-emerald-400">
                                              {{ v }}
                                              <button v-if="(!filters.upt_id && !filters.is_upt_user) && v > 0 && isTargetBreakdown(findName(detail_napis, k, 'nama_registrasidetailnapi'))" 
                                                      @click="openBreakdown(
                                                          findName(detail_napis, k, 'nama_registrasidetailnapi') === 'MT' ? 'MT (Pidana Mati)' : 
                                                          (findName(detail_napis, k, 'nama_registrasidetailnapi') === 'SH' ? 'SH (Pidana Seumur Hidup)' : 
                                                          findName(detail_napis, k, 'nama_registrasidetailnapi')), 
                                                          d.breakdown_rekap_detail_napi?.[k]
                                                      )" 
                                                      class="text-[9px] bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:bg-emerald-900/50 border border-emerald-200 dark:border-emerald-800/50 px-1.5 py-0.5 rounded shadow-sm flex items-center gap-1 transition-colors">
                                                  <span>🔍</span> Detail
                                              </button>
                                          </span>
                                      </li>
                                  </ul>
                              </div>
                              <div class="rounded-lg bg-blue-50/40 dark:bg-blue-900/20 p-3 border border-blue-100 dark:border-blue-900/50">
                                  <h4 class="font-bold text-xs text-blue-700 dark:text-blue-400 mb-2 border-b pb-1">Detail Rekap Tahanan</h4>
                                  <ul class="space-y-1"><li v-for="(v, k) in d.rekap_detail_tahanan" :key="k" class="flex justify-between text-xs"><span class="text-muted-foreground">{{ findName(detail_tahanans, k, 'nama_registrasidetailtahanan') }}</span><span class="font-bold text-blue-700 dark:text-blue-400">{{ v }}</span></li></ul>
                              </div>

                              <div class="rounded-xl border bg-card p-3 shadow-sm flex flex-col md:col-span-2 xl:col-span-2">
                                  <h3 class="font-bold text-xs mb-3 border-b pb-2 text-primary">Rekapitulasi Tindak Pidana</h3>
                                  <div class="grid md:grid-cols-2 gap-4 flex-1">
                                      <div>
                                          <h4 class="font-bold text-xs text-red-600 dark:text-red-400 mb-2">Pidsus</h4>
                                          <ul class="space-y-1"><li v-for="(v, k) in d.rekap_pidsus" :key="k" class="flex justify-between text-[10px]"><span class="text-muted-foreground">{{ findName(pidsuses, k, 'nama_registrasipidsus') }}</span><span class="font-bold">{{ v }}</span></li></ul>
                                          <div class="mt-2 p-1 rounded border text-[10px] font-bold text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/30 flex justify-between"><span>Sub-Total Pidsus:</span><span>{{ getTotalPidsus(d) }}</span></div>
                                      </div>
                                      <div>
                                          <h4 class="font-bold text-xs text-orange-600 dark:text-orange-400 mb-2">Pidum</h4>
                                          <ul class="space-y-1"><li v-for="(v, k) in d.rekap_pidum" :key="k" class="flex justify-between text-[10px]"><span class="text-muted-foreground">{{ findName(pidums, k, 'nama_registrasipidum') }}</span><span class="font-bold">{{ v }}</span></li></ul>
                                          <div class="mt-2 p-1 rounded border text-[10px] font-bold text-orange-700 dark:text-orange-400 bg-orange-50 flex justify-between"><span>Sub-Total Pidum:</span><span>{{ getTotalPidum(d) }}</span></div>
                                      </div>
                                  </div>

                                  <div :class="getTotalPidsusPidum(d) === getTotalIsi(d) ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-100 dark:border-emerald-900/50 text-emerald-700 dark:text-emerald-400' : 'bg-red-50 dark:bg-red-900/30 border-red-100 dark:border-red-900/50 text-red-700 dark:text-red-400'" class="mt-3 p-1.5 rounded border text-[10px] font-bold flex flex-col transition-colors">
                                      <div class="flex justify-between"><span>Total Pidsus + Pidum (Tanpa Residivis): {{ getTotalPidsusPidum(d) }}</span><span v-if="getTotalPidsusPidum(d) === getTotalIsi(d)">✅ Sesuai Total Isi</span></div>
                                      <span v-if="getTotalPidsusPidum(d) !== getTotalIsi(d)" class="text-right">⚠️ Selisih: {{ Math.abs(getTotalPidsusPidum(d) - getTotalIsi(d)) }} dari Total Isi</span>
                                  </div>
                              </div>

                              <div class="rounded-lg bg-card border p-3">
                                  <h4 class="font-bold text-xs text-amber-600 dark:text-amber-500 mb-2 border-b pb-1">Overstaying</h4>
                                  <ul class="space-y-3">
                                      <li v-for="(v, k) in d.rekap_overstaying" :key="k" class="flex flex-col text-xs border-b border-amber-50 dark:border-amber-900/30 pb-2 last:border-0 last:pb-0">
                                          <div class="flex justify-between items-center">
                                              <span class="text-muted-foreground">{{ findName(overstayings, k, 'nama_registrasioverstaying') }}</span>
                                              <span class="font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30 px-2 py-0.5 rounded">{{ typeof v === 'object' && v !== null ? v.jumlah : v }}</span>
                                          </div>
                                          
                                          <div v-if="typeof v === 'object' && v !== null && v.jumlah > 0" class="mt-2">
                                              <div v-if="v.detail_keterangan && v.detail_keterangan.length > 0" class="max-h-48 overflow-y-auto space-y-1.5 pr-1 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-thumb]:bg-amber-200 [&::-webkit-scrollbar-thumb]:rounded-full">
                                                  <div v-for="(ket, idx) in v.detail_keterangan" :key="idx" class="text-[10px] text-amber-900 dark:text-amber-200 bg-amber-50 dark:bg-amber-900/30 p-2 rounded-lg border border-amber-200/60 shadow-sm flex flex-col">
                                                      <span class="font-bold border-b border-amber-200/50 pb-1 mb-1">{{ ket.upt }}</span>
                                                      <span class="leading-relaxed">{{ ket.text }}</span>
                                                  </div>
                                              </div>
                                              
                                              <div v-else-if="v.keterangan && v.keterangan.toLowerCase() !== 'nihil' && v.keterangan !== '-'" class="text-[10px] text-amber-800 dark:text-amber-300 bg-amber-100/50 dark:bg-amber-900/40 p-1.5 rounded border border-amber-200/50">
                                                  Ket: {{ v.keterangan }}
                                              </div>
                                          </div>
                                      </li>
                                  </ul>
                              </div>

                              <div class="rounded-lg bg-card border p-3 flex flex-col justify-between">
                                  <div>
                                      <h4 class="font-bold text-xs text-blue-600 dark:text-blue-400 mb-2 border-b pb-1">Kependudukan</h4>
                                      <ul class="space-y-1"><li v-for="(v, k) in d.rekap_identitas" :key="k" class="flex justify-between text-xs"><span class="text-muted-foreground">{{ findName(identitases, k, 'nama_registrasiidentitas') }}</span><span class="font-bold">{{ v }}</span></li></ul>
                                  </div>
                                  <div>
                                      <div :class="getTotalNik(d) === getTotalIsi(d) ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-100 dark:border-emerald-900/50 text-emerald-700 dark:text-emerald-400' : 'bg-red-50 dark:bg-red-900/30 border-red-100 dark:border-red-900/50 text-red-700 dark:text-red-400'" class="mt-2 p-1.5 rounded border text-[10px] font-bold flex justify-between items-center transition-colors"><span>Total NIK: {{ getTotalNik(d) }}</span><span v-if="getTotalNik(d) !== getTotalIsi(d)">⚠️ Selisih: {{ Math.abs(getTotalNik(d) - getTotalIsi(d)) }}</span><span v-else>✅ Sesuai</span></div>
                                      <div :class="getTotalKtp(d) === getTotalIsi(d) ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-100 dark:border-emerald-900/50 text-emerald-700 dark:text-emerald-400' : 'bg-red-50 dark:bg-red-900/30 border-red-100 dark:border-red-900/50 text-red-700 dark:text-red-400'" class="mt-1.5 p-1.5 rounded border text-[10px] font-bold flex justify-between items-center transition-colors"><span>Total KTP: {{ getTotalKtp(d) }}</span><span v-if="getTotalKtp(d) !== getTotalIsi(d)">⚠️ Selisih: {{ Math.abs(getTotalKtp(d) - getTotalIsi(d)) }}</span><span v-else>✅ Sesuai</span></div>
                                  </div>
                              </div>

                              <div class="rounded-lg bg-card border p-3 flex flex-col justify-between">
                                  <div>
                                      <h4 class="font-bold text-xs text-indigo-600 dark:text-indigo-400 mb-2 border-b pb-1">Agama</h4>
                                      <ul class="space-y-1"><li v-for="(v, k) in d.rekap_agama" :key="k" class="flex justify-between text-xs"><span class="text-muted-foreground">{{ findName(agamas, k, 'nama_agama') }}</span><span class="font-bold">{{ v }}</span></li></ul>
                                  </div>
                                  <div :class="objTotal(d.rekap_agama) === getTotalIsi(d) ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-100 dark:border-emerald-900/50 text-emerald-700 dark:text-emerald-400' : 'bg-red-50 dark:bg-red-900/30 border-red-100 dark:border-red-900/50 text-red-700 dark:text-red-400'" class="mt-2 p-1.5 rounded border text-[10px] font-bold flex justify-between items-center transition-colors"><span>Total: {{ objTotal(d.rekap_agama) }}</span><span v-if="objTotal(d.rekap_agama) !== getTotalIsi(d)">⚠️ Selisih: {{ Math.abs(objTotal(d.rekap_agama) - getTotalIsi(d)) }}</span><span v-else>✅ Sesuai</span></div>
                              </div>

                              <div class="rounded-lg bg-card border p-3"><h4 class="font-bold text-xs text-emerald-600 dark:text-emerald-400 mb-2 border-b pb-1">Integrasi</h4><ul class="space-y-1"><li v-for="(v, k) in d.rekap_integrasi" :key="k" class="flex justify-between text-xs"><span class="text-muted-foreground">{{ findName(integrasis, k, 'nama_integrasi') }}</span><span class="font-bold">{{ v }}</span></li></ul></div>
                              <div class="rounded-lg bg-card border p-3"><h4 class="font-bold text-xs text-teal-600 dark:text-teal-400 mb-2 border-b pb-1">Pengeluaran</h4><ul class="space-y-1"><li v-for="(v, k) in d.rekap_pengeluaran" :key="k" class="flex justify-between text-xs"><span class="text-muted-foreground">{{ findName(pengeluarans, k, 'nama_pengeluaran') }}</span><span class="font-bold">{{ v }}</span></li></ul></div>
                          </div>
                      </div>

                      <div class="mt-6">
                          <h3 class="text-sm font-black text-orange-700 dark:text-orange-400 uppercase tracking-wide border-b border-orange-200 dark:border-orange-800/50 pb-2 mb-4">B. Laporan Kunjungan & Layanan</h3>
                          <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                              <div v-if="d.rekap_petugas" class="rounded-xl bg-orange-50/30 dark:bg-orange-900/20 p-4 border border-orange-100 dark:border-orange-900/50 shadow-sm"><h4 class="font-bold text-xs text-orange-700 dark:text-orange-400 mb-3 border-b border-orange-200 dark:border-orange-800/50 pb-1">Daftar Petugas Jaga</h4><ul class="space-y-1.5"><li v-for="(v, k) in d.rekap_petugas" :key="k" class="flex justify-between text-xs items-center"><span class="text-muted-foreground">{{ findName(petugases, k, 'nama_registrasipetugas') }}</span><span class="font-black text-orange-800 dark:text-orange-300 bg-card px-2 py-0.5 rounded shadow-sm">{{ v }}</span></li></ul></div>
                              <div v-if="d.rekap_pengunjung" class="rounded-xl bg-orange-50/30 dark:bg-orange-900/20 p-4 border border-orange-100 dark:border-orange-900/50 shadow-sm"><h4 class="font-bold text-xs text-orange-700 dark:text-orange-400 mb-3 border-b border-orange-200 dark:border-orange-800/50 pb-1">Daftar Pengunjung</h4><ul class="space-y-1.5"><li v-for="(v, k) in d.rekap_pengunjung" :key="k" class="flex justify-between text-xs items-center"><span class="text-muted-foreground">{{ findName(pengunjungs, k, 'nama_registrasipengunjung') }}</span><span class="font-black text-orange-800 dark:text-orange-300 bg-card px-2 py-0.5 rounded shadow-sm">{{ v }}</span></li></ul></div>
                              <div v-if="d.rekap_wbp_dikunjungi" class="rounded-xl bg-orange-50/30 dark:bg-orange-900/20 p-4 border border-orange-100 dark:border-orange-900/50 shadow-sm"><h4 class="font-bold text-xs text-orange-700 dark:text-orange-400 mb-3 border-b border-orange-200 dark:border-orange-800/50 pb-1">WBP Yang Dikunjungi</h4><ul class="space-y-1.5"><li v-for="(v, k) in d.rekap_wbp_dikunjungi" :key="k" class="flex justify-between text-xs items-center"><span class="text-muted-foreground">{{ findName(wbp_dikunjungis, k, 'nama_registrasiwbpdikunjungi') }}</span><span class="font-black text-orange-800 dark:text-orange-300 bg-card px-2 py-0.5 rounded shadow-sm">{{ v }}</span></li></ul></div>
                              <div v-if="d.rekap_wbp_vidcall" class="rounded-xl bg-orange-50/30 dark:bg-orange-900/20 p-4 border border-orange-100 dark:border-orange-900/50 shadow-sm"><h4 class="font-bold text-xs text-orange-700 dark:text-orange-400 mb-3 border-b border-orange-200 dark:border-orange-800/50 pb-1">Layanan Video Call</h4><ul class="space-y-1.5"><li v-for="(v, k) in d.rekap_wbp_vidcall" :key="k" class="flex justify-between text-xs items-center"><span class="text-muted-foreground">{{ findName(wbp_vidcalls, k, 'nama_registrasiwbpvidcall') }}</span><span class="font-black text-orange-800 dark:text-orange-300 bg-card px-2 py-0.5 rounded shadow-sm">{{ v }}</span></li></ul></div>
                              <div v-if="d.rekap_wbp_wartel" class="rounded-xl bg-orange-50/30 dark:bg-orange-900/20 p-4 border border-orange-100 dark:border-orange-900/50 shadow-sm"><h4 class="font-bold text-xs text-orange-700 dark:text-orange-400 mb-3 border-b border-orange-200 dark:border-orange-800/50 pb-1">Layanan Wartel</h4><ul class="space-y-1.5"><li v-for="(v, k) in d.rekap_wbp_wartel" :key="k" class="flex justify-between text-xs items-center"><span class="text-muted-foreground">{{ findName(wbp_wartels, k, 'nama_registrasiwbpwartel') }}</span><span class="font-black text-orange-800 dark:text-orange-300 bg-card px-2 py-0.5 rounded shadow-sm">{{ v }}</span></li></ul></div>
                              <div v-if="d.rekap_barang_titipan" class="rounded-xl bg-orange-50/30 dark:bg-orange-900/20 p-4 border border-orange-100 dark:border-orange-900/50 shadow-sm"><h4 class="font-bold text-xs text-orange-700 dark:text-orange-400 mb-3 border-b border-orange-200 dark:border-orange-800/50 pb-1">Barang Titipan</h4><ul class="space-y-1.5"><li v-for="(v, k) in d.rekap_barang_titipan" :key="k" class="flex justify-between text-xs items-center"><span class="text-muted-foreground">{{ findName(barang_titipans, k, 'nama_registrasibarangtitipan') }}</span><span class="font-black text-orange-800 dark:text-orange-300 bg-card px-2 py-0.5 rounded shadow-sm">{{ v }}</span></li></ul></div>
                          </div>
                      </div>
                  </div>
              </div>
          </div>

          <div v-else-if="modul !== 'ringkasan'" class="animate-in fade-in slide-in-from-bottom-2 duration-300">
              <h2 class="text-lg font-bold mb-4 capitalize">Tabel Data: {{ modul }}</h2>
              <div class="bg-slate-100 dark:bg-slate-800 rounded-xl p-4 text-xs overflow-auto max-h-[400px]"><pre>{{ data_dinamis }}</pre></div>
          </div>
      </div>
    </div>
  </AppLayout>
</template>