<?php

namespace App\Exports;

use App\Models\InventoryTransaction;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class TransactionsExport
{
    public function __construct(
        private Carbon $from,
        private Carbon $to
    ) {}

    public function download(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $spreadsheet = $this->build();
        $filename    = 'transactions_' . $this->from->format('Y-m-d') . '_to_' . $this->to->format('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    private function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Inventory Hub')
            ->setTitle('Transaction Report')
            ->setDescription("Transactions {$this->from->format('M d, Y')} – {$this->to->format('M d, Y')}");

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Transactions');

        // ── Column widths ──────────────────────────────────────
        $widths = [8, 14, 10, 32, 16, 10, 10, 13, 12, 12, 30, 30, 20];
        foreach ($widths as $i => $width) {
            $sheet->getColumnDimensionByColumn($i + 1)->setWidth($width);
        }

        // ── Header row ─────────────────────────────────────────
        $headers = [
            'ID', 'Date', 'Time', 'Item Name', 'Category',
            'Type', 'Quantity', 'Stock Before', 'Stock After',
            'Net Change', 'Reason', 'Notes', 'Processed By',
        ];

        foreach ($headers as $col => $heading) {
            $cell = Coordinate::stringFromColumnIndex($col + 1) . '1';
            $sheet->setCellValue($cell, $heading);
        }

        $lastCol = Coordinate::stringFromColumnIndex(count($headers));

        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size'  => 10,
                'name'  => 'Calibri',
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1C2230'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->freezePane('A2');

        // ── Data rows ──────────────────────────────────────────
        $transactions = InventoryTransaction::with(['item:id,name,category', 'user:id,name'])
            ->whereBetween('created_at', [$this->from, $this->to])
            ->orderBy('created_at', 'desc')
            ->get();

        $row = 2;
        foreach ($transactions as $tx) {
            $rowData = [
                $tx->id,
                $tx->created_at->format('Y-m-d'),
                $tx->created_at->format('H:i:s'),
                $tx->item->name     ?? 'Deleted item',
                $tx->item->category ?? '—',
                strtoupper($tx->type),
                $tx->quantity,
                $tx->quantity_before,
                $tx->quantity_after,
                $tx->netChange(),
                $tx->reason,
                $tx->notes ?? '',
                $tx->user->name ?? 'Deleted user',
            ];

            foreach ($rowData as $col => $value) {
                $cell = Coordinate::stringFromColumnIndex($col + 1) . $row;
                $sheet->setCellValue($cell, $value);
            }

            // Zebra striping
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF6F8FA');
            }

            // Type column (F) conditional colour
            $type = strtoupper($tx->type);
            if ($type === 'IN') {
                $sheet->getStyle("F{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FF1A7F37']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE6FFED']],
                ]);
            } else {
                $sheet->getStyle("F{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFCF222E']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF0F0']],
                ]);
            }

            // Net change column (J) colour
            $netChange = $tx->netChange();
            $sheet->getStyle("J{$row}")->getFont()
                ->setBold(true)
                ->getColor()->setARGB($netChange >= 0 ? 'FF1A7F37' : 'FFCF222E');

            $row++;
        }

        $lastRow = $row - 1;

        // ── Table border ───────────────────────────────────────
        if ($lastRow >= 2) {
            $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray([
                'borders' => [
                    'outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF1C2230']],
                    'inside'  => ['borderStyle' => Border::BORDER_THIN,   'color' => ['argb' => 'FFD0D7DE']],
                ],
            ]);

            // Centre numeric columns
            foreach (['A', 'G', 'H', 'I', 'J'] as $col) {
                $sheet->getStyle("{$col}2:{$col}{$lastRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        // ── Summary footer ─────────────────────────────────────
        $summaryRow = $lastRow + 2;
        $summaryData = [
            ['Report Period:',        $this->from->format('M d, Y') . ' – ' . $this->to->format('M d, Y')],
            ['Generated:',            now()->format('M d, Y H:i')],
            ['Total Transactions:',   $lastRow - 1],
            ['Total Stocked In:',     "=SUMIF(F2:F{$lastRow},\"IN\",G2:G{$lastRow})"],
            ['Total Stocked Out:',    "=SUMIF(F2:F{$lastRow},\"OUT\",G2:G{$lastRow})"],
        ];

        foreach ($summaryData as $i => $pair) {
            $r = $summaryRow + $i;
            $sheet->setCellValue("K{$r}", $pair[0]);
            $sheet->setCellValue("L{$r}", $pair[1]);
            $sheet->getStyle("K{$r}")->getFont()->setBold(true)->getColor()->setARGB('FF6E7681');
        }

        // Colour IN/OUT totals
        $sheet->getStyle("L" . ($summaryRow + 3))->getFont()->setBold(true)->getColor()->setARGB('FF1A7F37');
        $sheet->getStyle("L" . ($summaryRow + 4))->getFont()->setBold(true)->getColor()->setARGB('FFCF222E');

        return $spreadsheet;
    }
}
