<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class RekapIntegrasiExport
{
    protected $dari;
    protected $sampai;
    protected $jenisIntegrasis;
    protected $perUpt;
    protected $grandTotal;

    public function __construct($dari, $sampai, $jenisIntegrasis, $perUpt, $grandTotal)
    {
        $this->dari            = $dari;
        $this->sampai          = $sampai;
        $this->jenisIntegrasis = $jenisIntegrasis;
        $this->perUpt          = $perUpt;
        $this->grandTotal      = $grandTotal;
    }

    public function download(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Integrasi');

        // ─── Hitung jumlah kolom ───────────────────────────────────────────────
        // Kolom: No | Tanggal/Nama UPT | [jenis integrasi...] | Total
        $totalJenis = count($this->jenisIntegrasis);
        $totalCols  = 2 + $totalJenis + 1; // No + Tanggal + jenis + Total
        $lastCol    = $this->numToAlpha($totalCols); // e.g. 'F'

        $row = 1;

        // ─── JUDUL UTAMA ──────────────────────────────────────────────────────
        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", 'REKAP INTEGRASI WARGA BINAAN PEMASYARAKATAN');
        $this->styleJudul($sheet, "A{$row}:{$lastCol}{$row}", 14);
        $row++;

        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", 'Per Unit Pelaksana Teknis (UPT)');
        $this->styleJudul($sheet, "A{$row}:{$lastCol}{$row}", 11, false);
        $row++;

        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $periodeLabel = 'Periode: ' . $this->formatTanggal($this->dari) . ' s.d. ' . $this->formatTanggal($this->sampai);
        $sheet->setCellValue("A{$row}", $periodeLabel);
        $this->styleJudul($sheet, "A{$row}:{$lastCol}{$row}", 11, false, 'FFFEF9C3');
        $row++;
        $row++; // baris kosong

        // ─── Loop per UPT ─────────────────────────────────────────────────────
        foreach ($this->perUpt as $idx => $uptData) {

            // Header UPT
            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $noUpt = ($idx + 1) . '. ' . strtoupper($uptData['upt_name']);
            $sheet->setCellValue("A{$row}", $noUpt);
            $this->styleHeaderUpt($sheet, "A{$row}:{$lastCol}{$row}");
            $row++;

            if (!empty($uptData['rows'])) {
                // Sub-header kolom
                $this->writeSubHeader($sheet, $row, $totalCols);
                $row++;

                // Baris data harian
                $rowIndex = 1;
                foreach ($uptData['rows'] as $dataRow) {
                    $col = 1;
                    $sheet->setCellValueByColumnAndRow($col++, $row, $rowIndex);
                    $sheet->setCellValueByColumnAndRow($col++, $row, $this->formatTanggal($dataRow['tanggal']));

                    $rowTotal = 0;
                    foreach ($this->jenisIntegrasis as $jenis) {
                        $val = (int)($dataRow['rekap'][$jenis['id']] ?? 0);
                        $sheet->setCellValueByColumnAndRow($col++, $row, $val);
                        $rowTotal += $val;
                    }
                    $sheet->setCellValueByColumnAndRow($col, $row, $rowTotal);

                    $this->styleDataRow($sheet, "A{$row}:{$lastCol}{$row}", $rowIndex % 2 === 0);
                    $rowIndex++;
                    $row++;
                }

                // Baris SUBTOTAL
                $col = 1;
                $sheet->setCellValueByColumnAndRow($col++, $row, '');
                $sheet->setCellValueByColumnAndRow($col++, $row, 'SUBTOTAL');

                $subtotalTotal = 0;
                foreach ($this->jenisIntegrasis as $jenis) {
                    $val = (int)($uptData['subtotal'][$jenis['id']] ?? 0);
                    $sheet->setCellValueByColumnAndRow($col++, $row, $val);
                    $subtotalTotal += $val;
                }
                $sheet->setCellValueByColumnAndRow($col, $row, $subtotalTotal);
                $this->styleSubtotal($sheet, "A{$row}:{$lastCol}{$row}");
                $row++;

            } else {
                // Nihil
                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                $sheet->setCellValue("A{$row}", 'Nihil — tidak ada data integrasi pada periode ini.');
                $this->styleNihil($sheet, "A{$row}:{$lastCol}{$row}");
                $row++;
            }

            $row++; // spasi antar UPT
        }

        // ─── GRAND TOTAL ──────────────────────────────────────────────────────
        $row++; // spasi sebelum grand total
        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", '📊 REKAPITULASI TOTAL KANWIL');
        $this->styleHeaderGrand($sheet, "A{$row}:{$lastCol}{$row}");
        $row++;

        // Sub-header grand total (No | Nama UPT | jenis... | Total)
        $this->writeSubHeaderGrand($sheet, $row, $totalCols);
        $row++;

        // Baris per UPT di tabel grand
        $rowIndex = 1;
        foreach ($this->perUpt as $uptData) {
            $col = 1;
            $sheet->setCellValueByColumnAndRow($col++, $row, $rowIndex);
            $sheet->setCellValueByColumnAndRow($col++, $row, $uptData['upt_name']);

            $rowTotal = 0;
            foreach ($this->jenisIntegrasis as $jenis) {
                $val = (int)($uptData['subtotal'][$jenis['id']] ?? 0);
                $sheet->setCellValueByColumnAndRow($col++, $row, $val);
                $rowTotal += $val;
            }
            $sheet->setCellValueByColumnAndRow($col, $row, $rowTotal);

            $this->styleDataRow($sheet, "A{$row}:{$lastCol}{$row}", $rowIndex % 2 === 0);
            $rowIndex++;
            $row++;
        }

        // Baris GRAND TOTAL
        $col = 1;
        $sheet->setCellValueByColumnAndRow($col++, $row, '');
        $sheet->setCellValueByColumnAndRow($col++, $row, 'GRAND TOTAL');

        $grandTotalKeseluruhan = 0;
        foreach ($this->jenisIntegrasis as $jenis) {
            $val = (int)($this->grandTotal[$jenis['id']] ?? 0);
            $sheet->setCellValueByColumnAndRow($col++, $row, $val);
            $grandTotalKeseluruhan += $val;
        }
        $sheet->setCellValueByColumnAndRow($col, $row, $grandTotalKeseluruhan);
        $this->styleGrandTotal($sheet, "A{$row}:{$lastCol}{$row}");
        $row++;

        // ─── Footer ──────────────────────────────────────────────────────────
        $row++;
        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", 'Dicetak pada: ' . now()->locale('id')->isoFormat('DD MMMM YYYY, HH:mm') . ' WIB');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF94A3B8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);

        // ─── Set column widths ────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(5);   // No
        $sheet->getColumnDimension('B')->setWidth(28);  // Tanggal / Nama UPT
        for ($c = 3; $c <= $totalCols; $c++) {
            $sheet->getColumnDimensionByColumn($c)->setWidth(14);
        }

        // ─── Stream download ─────────────────────────────────────────────────
        $writer   = new Xlsx($spreadsheet);
        $filename = 'Rekap_Integrasi_' . str_replace('-', '', $this->dari) . '_sd_' . str_replace('-', '', $this->sampai) . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function numToAlpha(int $num): string
    {
        $alpha = '';
        while ($num > 0) {
            $num--;
            $alpha = chr($num % 26 + 65) . $alpha;
            $num   = (int)floor($num / 26);
        }
        return $alpha;
    }

    private function formatTanggal(string $tgl): string
    {
        return date('d/m/Y', strtotime($tgl));
    }

    private function writeSubHeader($sheet, int $row, int $totalCols): void
    {
        $headers = ['No', 'Tanggal'];
        foreach ($this->jenisIntegrasis as $jenis) {
            $headers[] = $jenis['nama_integrasi'];
        }
        $headers[] = 'Total';

        foreach ($headers as $i => $header) {
            $sheet->setCellValueByColumnAndRow($i + 1, $row, $header);
        }

        $lastCol = $this->numToAlpha($totalCols);
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF334155']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF1F5F9']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        // Kolom B rata kiri
        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getRowDimension($row)->setRowHeight(22);
    }

    private function writeSubHeaderGrand($sheet, int $row, int $totalCols): void
    {
        $headers = ['No', 'Nama UPT'];
        foreach ($this->jenisIntegrasis as $jenis) {
            $headers[] = $jenis['nama_integrasi'];
        }
        $headers[] = 'Total';

        foreach ($headers as $i => $header) {
            $sheet->setCellValueByColumnAndRow($i + 1, $row, $header);
        }

        $lastCol = $this->numToAlpha($totalCols);
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FFE2E8F0']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF334155']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getRowDimension($row)->setRowHeight(22);
    }

    // ── Style Helpers ─────────────────────────────────────────────────────────

    private function styleJudul($sheet, string $range, int $size = 12, bool $bold = true, string $bg = 'FFFFFFFF'): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font'      => ['bold' => $bold, 'size' => $size, 'color' => ['argb' => 'FF0F172A']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(explode(':', $range)[0] === $range 
            ? 1 
            : (int) filter_var(explode(':', $range)[0], FILTER_SANITIZE_NUMBER_INT)
        )->setRowHeight(20);
    }

    private function styleHeaderUpt($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF334155']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $rowNum = (int) filter_var(explode(':', $range)[0], FILTER_SANITIZE_NUMBER_INT);
        $sheet->getRowDimension($rowNum)->setRowHeight(20);
    }

    private function styleHeaderGrand($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFF8FAFC']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF334155']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $rowNum = (int) filter_var(explode(':', $range)[0], FILTER_SANITIZE_NUMBER_INT);
        $sheet->getRowDimension($rowNum)->setRowHeight(22);
    }

    private function styleDataRow($sheet, string $range, bool $even = false): void
    {
        $bg = $even ? 'FFF8FAFC' : 'FFFFFFFF';
        $sheet->getStyle($range)->applyFromArray([
            'font'      => ['size' => 9, 'color' => ['argb' => 'FF1E293B']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        // Kolom B (tanggal/nama) rata kiri
        $rowNum = (int) filter_var(explode(':', $range)[0], FILTER_SANITIZE_NUMBER_INT);
        $sheet->getStyleByColumnAndRow(2, $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        // Kolom A (no) abu-abu
        $sheet->getStyleByColumnAndRow(1, $rowNum)->getFont()->getColor()->setARGB('FF94A3B8');
        $sheet->getRowDimension($rowNum)->setRowHeight(16);
    }

    private function styleSubtotal($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF15803D']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF0FDF4']],
            'borders'   => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF86EFAC']],
                'top'        => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF22C55E']],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $rowNum = (int) filter_var(explode(':', $range)[0], FILTER_SANITIZE_NUMBER_INT);
        $sheet->getStyleByColumnAndRow(2, $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getRowDimension($rowNum)->setRowHeight(18);

        // Kolom Total (last) lebih tebal
        $totalCols = 2 + count($this->jenisIntegrasis) + 1;
        $sheet->getStyleByColumnAndRow($totalCols, $rowNum)->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF14532D']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDCFCE7']],
        ]);
    }

    private function styleGrandTotal($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF92400E']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF3C7']],
            'borders'   => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFF59E0B']],
                'top'        => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FFF59E0B']],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $rowNum = (int) filter_var(explode(':', $range)[0], FILTER_SANITIZE_NUMBER_INT);
        $sheet->getStyleByColumnAndRow(2, $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getRowDimension($rowNum)->setRowHeight(20);

        // Kolom Total grand → kuning tua
        $totalCols = 2 + count($this->jenisIntegrasis) + 1;
        $sheet->getStyleByColumnAndRow($totalCols, $rowNum)->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF78350F']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFDE68A']],
        ]);
    }

    private function styleNihil($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF94A3B8']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFAFAFA']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
    }
}
