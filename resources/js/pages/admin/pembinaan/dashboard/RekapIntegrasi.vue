<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { onMounted, computed } from 'vue'

const props = defineProps<{
    dari: string,
    sampai: string,
    jenis_integrasis: { id: number, nama_integrasi: string }[],
    per_upt: {
        upt_id: number,
        upt_name: string,
        rows: { tanggal: string, rekap: Record<number, number> }[],
        subtotal: Record<number, number>
    }[],
    grand_total: Record<number, number>
}>()

function formatTanggal(tgl: string) {
    if (!tgl) return '-'
    const d = new Date(tgl)
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
}

function rowTotal(rekap: Record<number, number>) {
    return props.jenis_integrasis.reduce((sum, j) => sum + (Number(rekap[j.id]) || 0), 0)
}

const grandTotalKeseluruhan = computed(() =>
    props.jenis_integrasis.reduce((sum, j) => sum + (Number(props.grand_total[j.id]) || 0), 0)
)

onMounted(() => {
    setTimeout(() => window.print(), 600)
})
</script>

<template>
    <Head :title="`Rekap Integrasi ${dari} s.d. ${sampai}`" />

    <div class="rekap-wrapper">
        <div class="no-print action-bar">
            <button @click="() => window.print()" class="btn-print">🖨️ Cetak Ulang</button>
            <button @click="() => window.close()" class="btn-close">✕ Tutup</button>
        </div>

        <div class="dokumen">
            <div class="kop">
                <h1>REKAP INTEGRASI WARGA BINAAN PEMASYARAKATAN</h1>
                <h2>Per Unit Pelaksana Teknis (UPT)</h2>
                <p class="periode">Periode: {{ formatTanggal(dari) }} s.d. {{ formatTanggal(sampai) }}</p>
                <div class="kop-divider"></div>
            </div>

            <div v-for="(uptData, idx) in per_upt" :key="uptData.upt_id" class="seksi-upt">
                <div class="header-upt">
                    <span class="no-upt">{{ idx + 1 }}.</span>
                    <span class="nama-upt">{{ uptData.upt_name }}</span>
                </div>

                <table v-if="uptData.rows.length > 0" class="tabel-rekap">
                    <thead>
                        <tr>
                            <th class="th-no">No</th>
                            <th class="th-tanggal">Tanggal</th>
                            <th v-for="jenis in jenis_integrasis" :key="jenis.id" class="th-jenis">
                                {{ jenis.nama_integrasi }}
                            </th>
                            <th class="th-total">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row, i) in uptData.rows" :key="i">
                            <td class="td-no">{{ i + 1 }}</td>
                            <td class="td-tanggal">{{ formatTanggal(row.tanggal) }}</td>
                            <td v-for="jenis in jenis_integrasis" :key="jenis.id" class="td-val">
                                {{ Number(row.rekap[jenis.id]) || 0 }}
                            </td>
                            <td class="td-subtotal-row">{{ rowTotal(row.rekap) }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="tr-subtotal">
                            <td colspan="2" class="td-label-subtotal">SUBTOTAL</td>
                            <td v-for="jenis in jenis_integrasis" :key="jenis.id" class="td-subtotal-val">
                                {{ Number(uptData.subtotal[jenis.id]) || 0 }}
                            </td>
                            <td class="td-subtotal-grand">
                                {{ jenis_integrasis.reduce((s, j) => s + (Number(uptData.subtotal[j.id]) || 0), 0) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <div v-else class="nihil-label">
                    Nihil — tidak ada data integrasi pada periode ini.
                </div>
            </div>

            <div class="seksi-grand-total">
                <div class="header-grand-total">
                    📊 REKAPITULASI TOTAL KANWIL
                </div>
                <table class="tabel-rekap tabel-grand">
                    <thead>
                        <tr>
                            <th class="th-no">No</th>
                            <th class="th-upt-nama">Nama UPT</th>
                            <th v-for="jenis in jenis_integrasis" :key="jenis.id" class="th-jenis">
                                {{ jenis.nama_integrasi }}
                            </th>
                            <th class="th-total">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(uptData, idx) in per_upt" :key="uptData.upt_id">
                            <td class="td-no">{{ idx + 1 }}</td>
                            <td class="td-upt-nama">{{ uptData.upt_name }}</td>
                            <td v-for="jenis in jenis_integrasis" :key="jenis.id" class="td-val">
                                {{ Number(uptData.subtotal[jenis.id]) || 0 }}
                            </td>
                            <td class="td-subtotal-row">
                                {{ jenis_integrasis.reduce((s, j) => s + (Number(uptData.subtotal[j.id]) || 0), 0) }}
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="tr-grand-total">
                            <td colspan="2" class="td-label-subtotal">GRAND TOTAL</td>
                            <td v-for="jenis in jenis_integrasis" :key="jenis.id" class="td-subtotal-val">
                                {{ Number(grand_total[jenis.id]) || 0 }}
                            </td>
                            <td class="td-subtotal-grand">{{ grandTotalKeseluruhan }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="footer-doc">
                <p>Dicetak pada: {{ new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
* { box-sizing: border-box; }

.rekap-wrapper {
    font-family: 'Arial', sans-serif;
    font-size: 11px;
    color: #1a1a1a;
    background: #f8f8f8;
    min-height: 100vh;
}

.dokumen {
    background: white;
    max-width: 1100px;
    margin: 0 auto;
    padding: 24px 32px;
}

.action-bar {
    background: #1e293b;
    padding: 10px 24px;
    display: flex;
    gap: 10px;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 100;
}

.btn-print {
    background: #059669;
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
}
.btn-print:hover { background: #047857; }

.btn-close {
    background: #475569;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
}
.btn-close:hover { background: #334155; }

.kop {
    text-align: center;
    margin-bottom: 24px;
}
.kop h1 {
    font-size: 15px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin: 0 0 4px;
}
.kop h2 {
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    margin: 0 0 6px;
}
.kop .periode {
    font-size: 12px;
    font-weight: 700;
    color: #0f172a;
    background: #f0fdf4;
    display: inline-block;
    padding: 4px 16px;
    border-radius: 20px;
    border: 1px solid #bbf7d0;
    margin: 4px 0 12px;
}
.kop-divider {
    border-bottom: 2.5px solid #0f172a;
    margin-top: 8px;
}

.seksi-upt {
    margin-bottom: 28px;
    page-break-inside: avoid;
}

.header-upt {
    background: #1e293b;
    color: white;
    padding: 6px 12px;
    font-weight: 700;
    font-size: 12px;
    border-radius: 6px 6px 0 0;
    display: flex;
    gap: 8px;
    align-items: center;
}
.no-upt { color: #94a3b8; }
.nama-upt { text-transform: uppercase; letter-spacing: 0.03em; }

.tabel-rekap {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #e2e8f0;
    font-size: 10px;
}
.tabel-rekap th {
    background: #f1f5f9;
    padding: 6px 8px;
    text-align: center;
    font-weight: 700;
    font-size: 9px;
    text-transform: uppercase;
    border: 1px solid #cbd5e1;
    color: #334155;
}
.th-tanggal, .th-upt-nama { text-align: left !important; min-width: 120px; }
.th-no { width: 30px; }
.th-jenis { min-width: 60px; }
.th-total { background: #e2e8f0 !important; min-width: 55px; }

.tabel-rekap td {
    padding: 5px 8px;
    border: 1px solid #e2e8f0;
    text-align: center;
    vertical-align: middle;
}
.td-no { color: #94a3b8; font-size: 9px; }
.td-tanggal, .td-upt-nama { text-align: left; font-weight: 500; color: #334155; }
.td-val { color: #1e293b; }
.td-subtotal-row { font-weight: 700; color: #0f172a; background: #f8fafc; }

.tr-subtotal td {
    background: #f0fdf4;
    border-top: 2px solid #86efac;
    font-weight: 700;
    color: #15803d;
}
.td-label-subtotal {
    text-align: center !important;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.td-subtotal-val { font-weight: 800; color: #15803d; }
.td-subtotal-grand {
    font-weight: 900;
    font-size: 12px;
    color: #14532d;
    background: #dcfce7 !important;
}

.nihil-label {
    border: 1px solid #e2e8f0;
    border-top: none;
    padding: 10px 16px;
    color: #94a3b8;
    font-style: italic;
    font-size: 11px;
    background: #fafafa;
    border-radius: 0 0 4px 4px;
}

.seksi-grand-total {
    margin-top: 36px;
    page-break-inside: avoid;
}
.header-grand-total {
    background: #0f172a;
    color: #f8fafc;
    padding: 8px 14px;
    font-weight: 900;
    font-size: 13px;
    border-radius: 6px 6px 0 0;
    letter-spacing: 0.04em;
}

.tabel-grand th {
    background: #1e293b !important;
    color: #e2e8f0 !important;
    border-color: #334155 !important;
}
.tabel-grand .th-total { background: #334155 !important; }

.tr-grand-total td {
    background: #fef3c7 !important;
    border-top: 2.5px solid #f59e0b;
    font-weight: 800;
    color: #92400e;
    font-size: 11px;
}
.tr-grand-total .td-subtotal-grand {
    background: #fde68a !important;
    font-size: 13px;
    color: #78350f;
}

.footer-doc {
    margin-top: 32px;
    padding-top: 10px;
    border-top: 1px solid #e2e8f0;
    text-align: right;
    color: #94a3b8;
    font-size: 10px;
}


/* --- Dark Mode Enhancements --- */
:global(.dark) .rekap-wrapper { background: #020817; color: #f8fafc; }
:global(.dark) .dokumen { background: #0f172a; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.5); }
:global(.dark) .kop h2 { color: #94a3b8; }
:global(.dark) .kop .periode { background: #064e3b; color: #a7f3d0; border-color: #047857; }
:global(.dark) .kop-divider { border-bottom-color: #334155; }
:global(.dark) .header-upt { background: #1e293b; color: #f1f5f9; }
:global(.dark) .tabel-rekap { border-color: #334155; }
:global(.dark) .tabel-rekap th { background: #1e293b; border-color: #334155; color: #cbd5e1; }
:global(.dark) .tabel-rekap td { border-color: #334155; }
:global(.dark) .td-tanggal, :global(.dark) .td-upt-nama { color: #e2e8f0; }
:global(.dark) .td-val { color: #f8fafc; }
:global(.dark) .td-subtotal-row { color: #f8fafc; background: #0f172a; }
:global(.dark) .tr-subtotal td { background: #064e3b; border-top-color: #059669; color: #6ee7b7; }
:global(.dark) .td-subtotal-val { color: #34d399; }
:global(.dark) .td-subtotal-grand { background: #065f46 !important; color: #a7f3d0; }
:global(.dark) .nihil-label { background: #0f172a; border-color: #334155; color: #94a3b8; }
:global(.dark) .header-grand-total { background: #020817; color: #f8fafc; }
:global(.dark) .tabel-grand th { background: #020817 !important; border-color: #1e293b !important; }
:global(.dark) .tabel-grand .th-total { background: #0f172a !important; }
:global(.dark) .tr-grand-total td { background: #451a03 !important; border-top-color: #b45309; color: #fcd34d; }
:global(.dark) .tr-grand-total .td-subtotal-grand { background: #78350f !important; color: #fde68a; }
:global(.dark) .footer-doc { border-top-color: #334155; color: #64748b; }

@media print {

    @page { size: A4 landscape; margin: 12mm 10mm; }
    .no-print { display: none !important; }
    .rekap-wrapper { background: white; padding: 0; }
    .dokumen { max-width: 100%; padding: 0; }
    .seksi-upt { page-break-inside: avoid; }
    .tabel-rekap { font-size: 9px; }
    .tabel-rekap th, .tabel-rekap td { padding: 4px 5px; }
}
</style>
