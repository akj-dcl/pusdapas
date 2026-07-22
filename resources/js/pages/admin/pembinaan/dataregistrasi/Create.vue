<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'
import { onMounted, watch, computed } from 'vue'

const props = defineProps<{
  upts: any[], umums: any[], pidsuses: any[], pidums: any[], overstayings: any[],
  integrasis: any[], identitases: any[], agamas: any[], pengeluarans: any[],
  pendidikans: any[], detail_napis: any[], detail_tahanans: any[], petugases: any[],
  pengunjungs: any[], wbp_dikunjungis: any[], wbp_vidcalls: any[], wbp_wartels: any[], barang_titipans: any[]
}>()

const form = useForm({
  tanggal: new Date().toISOString().split('T')[0],
  upt_id: props.upts?.[0]?.id ? String(props.upts[0].id) : '',
  
  rekap_umum: {} as Record<string, any>,
  rekap_pidsus: {} as Record<string, any>,
  rekap_pidum: {} as Record<string, any>, 
  rekap_overstaying: {} as Record<string, any>,
  rekap_integrasi: {} as Record<number, number>,
  rekap_identitas: {} as Record<number, number>,
  rekap_agama: {} as Record<number, number>,
  rekap_pengeluaran: {} as Record<number, number>,
  rekap_pendidikan: {} as Record<number, number>,
  rekap_detail_napi: {} as Record<number, number>,
  rekap_detail_tahanan: {} as Record<number, number>,
  rekap_petugas: {} as Record<number, number>,
  rekap_pengunjung: {} as Record<number, number>,
  rekap_wbp_dikunjungi: {} as Record<number, number>,
  rekap_wbp_vidcall: {} as Record<number, number>,
  rekap_wbp_wartel: {} as Record<number, number>,
  rekap_barang_titipan: {} as Record<number, number>,
})

const idTahanan = computed(() => props.umums?.find(i => i.nama_registrasiumum.toLowerCase().includes('tahanan'))?.id);
const idNapi = computed(() => props.umums?.find(i => i.nama_registrasiumum.toLowerCase().includes('napi') || i.nama_registrasiumum.toLowerCase().includes('narapidana'))?.id);
const idKapasitas = computed(() => props.umums?.find(i => i.nama_registrasiumum.toLowerCase().includes('kapasitas'))?.id);
const idTotalIsi = computed(() => props.umums?.find(i => i.nama_registrasiumum.toLowerCase().includes('total isi') || i.nama_registrasiumum.toLowerCase().includes('total_isi'))?.id);
const idOvercrowded = computed(() => props.umums?.find(i => i.nama_registrasiumum.toLowerCase().includes('overcrowded'))?.id);
const idWna = computed(() => props.umums?.find(i => i.nama_registrasiumum.toUpperCase().includes('WNA'))?.id);

const idTotalResidivis = computed(() => props.umums?.find(i => i.nama_registrasiumum.toLowerCase().includes('residivis'))?.id);
const idPidsusResidivis = computed(() => props.pidsuses?.find(i => i.nama_registrasipidsus.toLowerCase().includes('residivis'))?.id);
const idPidumResidivis = computed(() => props.pidums?.find(i => i.nama_registrasipidum.toLowerCase().includes('residivis'))?.id);

const idAdaNik = computed(() => props.identitases?.find(i => i.nama_registrasiidentitas.toLowerCase().includes('ada nik') && !i.nama_registrasiidentitas.toLowerCase().includes('tidak'))?.id);
const idTidakAdaNik = computed(() => props.identitases?.find(i => i.nama_registrasiidentitas.toLowerCase().includes('tidak ada nik'))?.id);
const idAdaKtp = computed(() => props.identitases?.find(i => i.nama_registrasiidentitas.toLowerCase().includes('ada ktp') && !i.nama_registrasiidentitas.toLowerCase().includes('tidak'))?.id);
const idTidakAdaKtp = computed(() => props.identitases?.find(i => i.nama_registrasiidentitas.toLowerCase().includes('tidak ada ktp'))?.id);

const totalIsiVal = computed(() => Number(form.rekap_umum[idTotalIsi.value]) || 0);

const totalPendidikan = computed(() => Object.values(form.rekap_pendidikan).reduce((a, b) => Number(a) + Number(b), 0));
const totalPidsus = computed(() => Object.entries(form.rekap_pidsus).reduce((sum, [key, val]) => String(key) === String(idPidsusResidivis.value) ? sum : sum + (Number(val) || 0), 0));
const totalPidum = computed(() => Object.entries(form.rekap_pidum).reduce((sum, [key, val]) => String(key) === String(idPidumResidivis.value) ? sum : sum + (Number(val) || 0), 0));
const totalPidsusPidum = computed(() => totalPidsus.value + totalPidum.value);
const totalNik = computed(() => (Number(form.rekap_identitas[idAdaNik.value]) || 0) + (Number(form.rekap_identitas[idTidakAdaNik.value]) || 0));
const totalKtp = computed(() => (Number(form.rekap_identitas[idAdaKtp.value]) || 0) + (Number(form.rekap_identitas[idTidakAdaKtp.value]) || 0));
const totalAgama = computed(() => Object.values(form.rekap_agama).reduce((a, b) => Number(a) + Number(b), 0));

onMounted(() => {
    props.umums?.forEach(item => { form.rekap_umum[item.id] = 0 })
    props.pidsuses?.forEach(item => { form.rekap_pidsus[item.id] = 0 })
    props.pidums?.forEach(item => { form.rekap_pidum[item.id] = 0 })
    props.overstayings?.forEach(item => { form.rekap_overstaying[item.id] = { jumlah: 0, keterangan: 'Nihil' } })
    props.integrasis?.forEach(item => { form.rekap_integrasi[item.id] = 0 })
    props.identitases?.forEach(item => { form.rekap_identitas[item.id] = 0 })
    props.agamas?.forEach(item => { form.rekap_agama[item.id] = 0 })
    props.pengeluarans?.forEach(item => { form.rekap_pengeluaran[item.id] = 0 })
    props.pendidikans?.forEach(item => { form.rekap_pendidikan[item.id] = 0 })
    props.detail_napis?.forEach(item => { form.rekap_detail_napi[item.id] = 0 })
    props.detail_tahanans?.forEach(item => { form.rekap_detail_tahanan[item.id] = 0 })
    props.petugases?.forEach(item => { form.rekap_petugas[item.id] = 0 })
    props.pengunjungs?.forEach(item => { form.rekap_pengunjung[item.id] = 0 })
    props.wbp_dikunjungis?.forEach(item => { form.rekap_wbp_dikunjungi[item.id] = 0 })
    props.wbp_vidcalls?.forEach(item => { form.rekap_wbp_vidcall[item.id] = 0 })
    props.wbp_wartels?.forEach(item => { form.rekap_wbp_wartel[item.id] = 0 })
    props.barang_titipans?.forEach(item => { form.rekap_barang_titipan[item.id] = 0 })
    
    form.rekap_umum['detail_wna'] = [];
})

function hitungTotalan() {
    let totalTahanan = 0;
    Object.values(form.rekap_detail_tahanan).forEach(val => { totalTahanan += Number(val) || 0; });
    if (idTahanan.value) form.rekap_umum[idTahanan.value] = totalTahanan;

    let totalNapi = 0;
    Object.values(form.rekap_detail_napi).forEach(val => { totalNapi += Number(val) || 0; });
    if (idNapi.value) form.rekap_umum[idNapi.value] = totalNapi;

    const totalIsi = totalTahanan + totalNapi;
    if (idTotalIsi.value) form.rekap_umum[idTotalIsi.value] = totalIsi;

    const kapasitas = idKapasitas.value ? (Number(form.rekap_umum[idKapasitas.value]) || 0) : 0;
    if (idOvercrowded.value) {
        if (kapasitas > 0) {
            const over = ((totalIsi - kapasitas) / kapasitas) * 100;
            form.rekap_umum[idOvercrowded.value] = over.toFixed(2) + '%';
        } else {
            form.rekap_umum[idOvercrowded.value] = '0%';
        }
    }

    let resiNarkoba = 0;
    if (idPidsusResidivis.value) resiNarkoba = Number(form.rekap_pidsus[idPidsusResidivis.value]) || 0;
    let resiPidum = 0;
    if (idPidumResidivis.value) resiPidum = Number(form.rekap_pidum[idPidumResidivis.value]) || 0;
    if (idTotalResidivis.value) form.rekap_umum[idTotalResidivis.value] = resiNarkoba + resiPidum;

    // 🛠️ GENERATOR WNA PINDAH KE SINI AGAR KEBAL ERROR DI VPS
    if (idWna.value) {
        const countWna = Number(form.rekap_umum[idWna.value]) || 0;
        if (!form.rekap_umum['detail_wna']) form.rekap_umum['detail_wna'] = [];
        
        if (form.rekap_umum['detail_wna'].length < countWna) {
            while (form.rekap_umum['detail_wna'].length < countWna) {
                form.rekap_umum['detail_wna'].push({ status: 'Tahanan', negara: '' });
            }
        } else if (form.rekap_umum['detail_wna'].length > countWna) {
            form.rekap_umum['detail_wna'].splice(countWna);
        }
    }
}

function cekKeteranganOverstaying(id: number) {
    if (form.rekap_overstaying[id].jumlah === 0 || form.rekap_overstaying[id].jumlah === '') {
        form.rekap_overstaying[id].keterangan = 'Nihil';
    } else {
        if (form.rekap_overstaying[id].keterangan === 'Nihil') form.rekap_overstaying[id].keterangan = '';
    }
}


function submit() { form.post('/admin/data-registrasis') }
</script>

<template>
  <Head title="Input Rekap Registrasi" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex items-center justify-between mb-2">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Input Rekap Registrasi Harian</h1>
            <p class="text-sm text-muted-foreground">Kalkulasi Hunian dan Kunjungan terhitung otomatis.</p>
        </div>
        <Link href="/admin/data-registrasis" class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium">Kembali</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2"><label class="text-sm font-bold">Tanggal Laporan</label><input v-model="form.tanggal" type="date" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" /></div>
                <div class="space-y-2"><label class="text-sm font-bold">Lokasi UPT</label>
                    <select v-model="form.upt_id" :disabled="upts.length === 1" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                        <option v-for="u in upts" :key="u.id" :value="String(u.id)">{{ u.name }}</option>
                    </select>
                </div>
            </div>
        </div>

        <h2 class="text-base font-bold text-emerald-700 tracking-tight">1. INPUT DETAIL TAHANAN & NAPI (PENGUNCI OTOMATIS)</h2>
        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-xl border bg-card p-4 shadow-sm flex flex-col">
                <h3 class="font-bold text-sm mb-3 border-b pb-2 text-blue-600">Detail Sub-Kategori Tahanan</h3>
                <div class="space-y-2"><div v-for="item in detail_tahanans" :key="item.id" class="flex justify-between items-center bg-muted/30 p-2 rounded-md"><label class="text-xs font-medium">{{ item.nama_registrasidetailtahanan }}</label><input v-model.number="form.rekap_detail_tahanan[item.id]" @input="hitungTotalan" type="number" min="0" class="w-20 h-8 rounded border px-2 text-xs text-right bg-background font-bold text-blue-700 focus:ring-blue-500" /></div></div>
            </div>
            <div class="rounded-xl border bg-card p-4 shadow-sm flex flex-col">
                <h3 class="font-bold text-sm mb-3 border-b pb-2 text-emerald-600">Detail Sub-Kategori Napi</h3>
                <div class="space-y-2"><div v-for="item in detail_napis" :key="item.id" class="flex justify-between items-center bg-muted/30 p-2 rounded-md"><label class="text-xs font-medium">{{ item.nama_registrasidetailnapi }}</label><input v-model.number="form.rekap_detail_napi[item.id]" @input="hitungTotalan" type="number" min="0" class="w-20 h-8 rounded border px-2 text-xs text-right bg-background font-bold text-emerald-700 focus:ring-emerald-500" /></div></div>
            </div>
        </div>

        <h2 class="text-base font-bold text-primary tracking-tight mt-6">2. DATA REGISTRASI UMUM & INDIKATOR UTAMA</h2>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            
            <div class="rounded-xl border bg-card p-4 shadow-sm flex flex-col">
                <h3 class="font-bold text-sm mb-3 border-b pb-2 text-primary">Data Registrasi Umum</h3>
                <div class="space-y-2">
                    <div v-for="item in umums" :key="item.id" class="flex justify-between items-center bg-muted/30 p-2 rounded-md">
                        <label class="text-xs font-medium">{{ item.nama_registrasiumum }}</label>
                        <input v-if="item.id === idTahanan || item.id === idNapi || item.id === idTotalIsi || item.id === idOvercrowded || item.id === idTotalResidivis" 
                               :value="form.rekap_umum[item.id]" type="text" readonly class="w-24 h-7 text-xs text-center rounded border bg-slate-100 font-black focus:outline-none cursor-not-allowed" 
                               :class="item.id === idOvercrowded ? 'text-red-600 bg-red-50' : (item.id === idTotalResidivis ? 'text-orange-600 bg-orange-50' : 'text-primary')" />
                        <input v-else v-model.number="form.rekap_umum[item.id]" @input="hitungTotalan" type="number" min="0" class="w-24 h-7 rounded border px-2 text-xs text-right bg-background focus:ring-primary" />
                    </div>
                </div>

                <div v-if="idWna && form.rekap_umum[idWna] > 0" class="mt-4 p-3 bg-blue-50/50 border border-blue-100 rounded-lg space-y-3 animate-fadeIn">
                    <p class="text-xs font-bold text-blue-800">🌐 Detail Kebangsaan WNA ({{ form.rekap_umum[idWna] }} Orang):</p>
                    <div v-for="(wna, index) in form.rekap_umum['detail_wna']" :key="index" class="p-2 border bg-white rounded shadow-sm space-y-2">
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-[10px] font-bold text-muted-foreground">WNA #{{ index + 1 }}</span>
                            <select v-model="wna.status" class="text-[10px] h-6 border rounded px-1"><option value="Tahanan">Tahanan</option><option value="Napi">Narapidana</option></select>
                        </div>
                        <input v-model="wna.negara" type="text" placeholder="Asal Negara" required class="w-full h-7 text-xs border rounded px-2" />
                    </div>
                </div>
            </div>

            <div class="rounded-xl border bg-card p-4 shadow-sm flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-sm mb-3 border-b pb-2 text-purple-600">Tingkat Pendidikan</h3>
                    <div class="space-y-2"><div v-for="item in pendidikans" :key="item.id" class="flex justify-between items-center bg-muted/30 p-2 rounded-md"><label class="text-xs font-medium">{{ item.nama_registrasipendidikan }}</label><input v-model.number="form.rekap_pendidikan[item.id]" type="number" min="0" class="w-16 h-7 rounded border px-2 text-xs text-right bg-background" /></div></div>
                </div>
                <div :class="totalPendidikan === totalIsiVal ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-red-50 border-red-200 text-red-700'" class="mt-3 p-2 rounded-lg border text-[10px] font-bold flex justify-between items-center transition-colors">
                    <span>Total: {{ totalPendidikan }}</span>
                    <span v-if="totalPendidikan !== totalIsiVal">⚠️ Selisih: {{ Math.abs(totalPendidikan - totalIsiVal) }}</span><span v-else>✅ Sesuai Total Isi</span>
                </div>
            </div>

            <div class="rounded-xl border bg-card p-4 shadow-sm flex flex-col md:col-span-2 xl:col-span-2">
                <h3 class="font-bold text-sm mb-3 border-b pb-2 text-primary">Rekapitulasi Tindak Pidana</h3>
                <div class="grid md:grid-cols-2 gap-4 flex-1">
                    <div>
                        <h4 class="font-bold text-xs text-red-600 mb-2">Pidsus</h4>
                        <div class="space-y-2">
                            <div v-for="item in pidsuses" :key="item.id" class="flex justify-between items-center bg-muted/30 p-1.5 rounded-md">
                                <label class="text-[10px] font-medium">{{ item.nama_registrasipidsus }}</label>
                                <input v-model.number="form.rekap_pidsus[item.id]" @input="hitungTotalan" type="number" min="0" class="w-14 h-6 rounded border px-1 text-[10px] text-right bg-background" />
                            </div>
                        </div>
                        <div class="mt-2 p-1.5 rounded border text-[10px] font-bold text-red-700 bg-red-50 flex justify-between">
                            <span>Sub-Total Pidsus:</span><span>{{ totalPidsus }}</span>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-bold text-xs text-orange-600 mb-2">Pidum</h4>
                        <div class="space-y-2">
                            <div v-for="item in pidums" :key="item.id" class="flex justify-between items-center bg-muted/30 p-1.5 rounded-md">
                                <label class="text-[10px] font-medium">{{ item.nama_registrasipidum }}</label>
                                <input v-model.number="form.rekap_pidum[item.id]" @input="hitungTotalan" type="number" min="0" class="w-14 h-6 rounded border px-1 text-[10px] text-right bg-background" />
                            </div>
                        </div>
                        <div class="mt-2 p-1.5 rounded border text-[10px] font-bold text-orange-700 bg-orange-50 flex justify-between">
                            <span>Sub-Total Pidum:</span><span>{{ totalPidum }}</span>
                        </div>
                    </div>
                </div>

                <div :class="totalPidsusPidum === totalIsiVal ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-red-50 border-red-200 text-red-700'" class="mt-4 p-2 rounded-lg border text-xs font-bold flex flex-col transition-colors">
                    <div class="flex justify-between">
                        <span>Total Pidsus + Pidum (Tanpa Residivis): {{ totalPidsusPidum }}</span>
                        <span v-if="totalPidsusPidum === totalIsiVal">✅ Sesuai</span>
                    </div>
                    <span v-if="totalPidsusPidum !== totalIsiVal" class="text-right">⚠️ Selisih: {{ Math.abs(totalPidsusPidum - totalIsiVal) }} dari Total Isi</span>
                </div>
            </div>

            <div class="rounded-xl border bg-card p-4 shadow-sm flex flex-col">
                <h3 class="font-bold text-sm mb-3 border-b pb-2 text-amber-600">Overstaying</h3>
                <div class="space-y-3">
                    <div v-for="item in overstayings" :key="item.id" class="flex flex-col bg-muted/30 p-2 rounded-md gap-2">
                        <template v-if="form.rekap_overstaying[item.id]">
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-medium">{{ item.nama_registrasioverstaying }}</label>
                                <input v-model.number="form.rekap_overstaying[item.id].jumlah" @input="cekKeteranganOverstaying(item.id)" type="number" min="0" class="w-16 h-7 rounded border px-2 text-xs text-right bg-background focus:ring-amber-500" />
                            </div>
                            <input v-if="form.rekap_overstaying[item.id].jumlah > 0" v-model="form.rekap_overstaying[item.id].keterangan" type="text" placeholder="Wajib isi keterangan/alasan..." required class="w-full h-7 rounded border border-amber-300 bg-white px-2 text-[10px] focus:ring-amber-500" />
                            <input v-else v-model="form.rekap_overstaying[item.id].keterangan" type="text" readonly class="w-full h-7 rounded border bg-slate-100 text-slate-400 px-2 text-[10px] italic cursor-not-allowed" />
                        </template>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border bg-card p-4 shadow-sm flex flex-col"><h3 class="font-bold text-sm mb-3 border-b pb-2 text-emerald-600">Integrasi</h3><div class="space-y-2"><div v-for="item in integrasis" :key="item.id" class="flex justify-between items-center bg-muted/30 p-2 rounded-md"><label class="text-xs font-medium">{{ item.nama_integrasi }}</label><input v-model.number="form.rekap_integrasi[item.id]" type="number" min="0" class="w-16 h-7 rounded border px-2 text-xs text-right bg-background" /></div></div></div>
            
            <div class="rounded-xl border bg-card p-4 shadow-sm flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-sm mb-3 border-b pb-2 text-blue-600">Kependudukan</h3>
                    <div class="space-y-2"><div v-for="item in identitases" :key="item.id" class="flex justify-between items-center bg-muted/30 p-2 rounded-md"><label class="text-xs font-medium">{{ item.nama_registrasiidentitas }}</label><input v-model.number="form.rekap_identitas[item.id]" type="number" min="0" class="w-16 h-7 rounded border px-2 text-xs text-right bg-background" /></div></div>
                </div>
                <div>
                    <div :class="totalNik === totalIsiVal ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-red-50 border-red-200 text-red-700'" class="mt-3 p-1.5 rounded border text-[10px] font-bold flex justify-between items-center transition-colors">
                        <span>Total NIK: {{ totalNik }}</span>
                        <span v-if="totalNik !== totalIsiVal">⚠️ Selisih: {{ Math.abs(totalNik - totalIsiVal) }}</span><span v-else>✅ Sesuai</span>
                    </div>
                    <div :class="totalKtp === totalIsiVal ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-red-50 border-red-200 text-red-700'" class="mt-1.5 p-1.5 rounded border text-[10px] font-bold flex justify-between items-center transition-colors">
                        <span>Total KTP: {{ totalKtp }}</span>
                        <span v-if="totalKtp !== totalIsiVal">⚠️ Selisih: {{ Math.abs(totalKtp - totalIsiVal) }}</span><span v-else>✅ Sesuai</span>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border bg-card p-4 shadow-sm flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-sm mb-3 border-b pb-2 text-indigo-600">Agama</h3>
                    <div class="space-y-2"><div v-for="item in agamas" :key="item.id" class="flex justify-between items-center bg-muted/30 p-2 rounded-md"><label class="text-xs font-medium">{{ item.nama_agama }}</label><input v-model.number="form.rekap_agama[item.id]" type="number" min="0" class="w-16 h-7 rounded border px-2 text-xs text-right bg-background" /></div></div>
                </div>
                <div :class="totalAgama === totalIsiVal ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-red-50 border-red-200 text-red-700'" class="mt-3 p-2 rounded-lg border text-[10px] font-bold flex justify-between items-center transition-colors">
                    <span>Total: {{ totalAgama }}</span>
                    <span v-if="totalAgama !== totalIsiVal">⚠️ Selisih: {{ Math.abs(totalAgama - totalIsiVal) }}</span><span v-else>✅ Sesuai</span>
                </div>
            </div>

            <div class="rounded-xl border bg-card p-4 shadow-sm flex flex-col"><h3 class="font-bold text-sm mb-3 border-b pb-2 text-teal-600">Pengeluaran</h3><div class="space-y-2"><div v-for="item in pengeluarans" :key="item.id" class="flex justify-between items-center bg-muted/30 p-2 rounded-md"><label class="text-xs font-medium">{{ item.nama_pengeluaran }}</label><input v-model.number="form.rekap_pengeluaran[item.id]" type="number" min="0" class="w-16 h-7 rounded border px-2 text-xs text-right bg-background" /></div></div></div>
        </div>

        <h2 class="text-base font-bold text-orange-600 tracking-tight mt-6">3. LAPORAN KUNJUNGAN & PELAYANAN HARIAN</h2>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-xl border bg-card p-4 shadow-sm"><h3 class="font-bold text-sm mb-3 border-b pb-2 text-orange-600">Daftar Petugas</h3><div class="space-y-2"><div v-for="item in petugases" :key="item.id" class="flex justify-between items-center bg-muted/30 p-2 rounded-md"><label class="text-xs font-medium">{{ item.nama_registrasipetugas }}</label><input v-model.number="form.rekap_petugas[item.id]" type="number" min="0" class="w-16 h-7 rounded border px-2 text-xs text-right bg-background" /></div></div></div>
            <div class="rounded-xl border bg-card p-4 shadow-sm"><h3 class="font-bold text-sm mb-3 border-b pb-2 text-orange-600">Daftar Pengunjung</h3><div class="space-y-2"><div v-for="item in pengunjungs" :key="item.id" class="flex justify-between items-center bg-muted/30 p-2 rounded-md"><label class="text-xs font-medium">{{ item.nama_registrasipengunjung }}</label><input v-model.number="form.rekap_pengunjung[item.id]" type="number" min="0" class="w-16 h-7 rounded border px-2 text-xs text-right bg-background" /></div></div></div>
            <div class="rounded-xl border bg-card p-4 shadow-sm"><h3 class="font-bold text-sm mb-3 border-b pb-2 text-orange-600">WBP Dikunjungi</h3><div class="space-y-2"><div v-for="item in wbp_dikunjungis" :key="item.id" class="flex justify-between items-center bg-muted/30 p-2 rounded-md"><label class="text-xs font-medium">{{ item.nama_registrasiwbpdikunjungi }}</label><input v-model.number="form.rekap_wbp_dikunjungi[item.id]" type="number" min="0" class="w-16 h-7 rounded border px-2 text-xs text-right bg-background" /></div></div></div>
            <div class="rounded-xl border bg-card p-4 shadow-sm"><h3 class="font-bold text-sm mb-3 border-b pb-2 text-orange-600">WBP Video Call</h3><div class="space-y-2"><div v-for="item in wbp_vidcalls" :key="item.id" class="flex justify-between items-center bg-muted/30 p-2 rounded-md"><label class="text-xs font-medium">{{ item.nama_registrasiwbpvidcall }}</label><input v-model.number="form.rekap_wbp_vidcall[item.id]" type="number" min="0" class="w-16 h-7 rounded border px-2 text-xs text-right bg-background" /></div></div></div>
            <div class="rounded-xl border bg-card p-4 shadow-sm"><h3 class="font-bold text-sm mb-3 border-b pb-2 text-orange-600">WBP Wartel</h3><div class="space-y-2"><div v-for="item in wbp_wartels" :key="item.id" class="flex justify-between items-center bg-muted/30 p-2 rounded-md"><label class="text-xs font-medium">{{ item.nama_registrasiwbpwartel }}</label><input v-model.number="form.rekap_wbp_wartel[item.id]" type="number" min="0" class="w-16 h-7 rounded border px-2 text-xs text-right bg-background" /></div></div></div>
            <div class="rounded-xl border bg-card p-4 shadow-sm"><h3 class="font-bold text-sm mb-3 border-b pb-2 text-orange-600">Barang Titipan</h3><div class="space-y-2"><div v-for="item in barang_titipans" :key="item.id" class="flex justify-between items-center bg-muted/30 p-2 rounded-md"><label class="text-xs font-medium">{{ item.nama_registrasibarangtitipan }}</label><input v-model.number="form.rekap_barang_titipan[item.id]" type="number" min="0" class="w-16 h-7 rounded border px-2 text-xs text-right bg-background" /></div></div></div>
        </div>

        <div class="flex justify-end pt-4 pb-8 border-t"><button type="submit" :disabled="form.processing" class="bg-primary text-primary-foreground px-10 py-3 rounded-xl font-bold shadow-lg">Simpan Laporan Harian</button></div>
      </form>
    </div>
  </AppLayout>
</template>