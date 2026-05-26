<?php

namespace App\Exports;

use App\Models\InventoryItem;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class InventorySnapshotExport
{
    public function download(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $spreadsheet = $this->build();
        $filename    = 'inventory_snapshot_' . now()->format('Y-m-d') . '.xlsx';

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
            ->setTitle('Inventory Snapshot')
            ->setDescription('Current stock levels as of ' . now()->format('M d, Y H:i'));

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inventory Snapshot');

        // ── Column widths ──────────────────────────────────────
        $widths = [8, 34, 16, 14, 12, 20, 14, 24, 18];
        foreach ($widths as $i => $width) {
            $sheet->getColumnDimensionByColumn($i + 1)->setWidth($width);
        }

        // ── Header row ─────────────────────────────────────────
        $headers = [
            'ID', 'Item Name', 'Category', 'Status',
            'Quantity', 'Low Stock Threshold', 'Stock Health',
            'Location', 'Last Updated',
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
        $items = InventoryItem::orderBy('category')->orderBy('name')->get();

        $row = 2;
        foreach ($items as $item) {
            $health = $item->quantity <= 0
                ? 'OUT OF STOCK'
                : ($item->isLowStock() ? 'LOW STOCK' : 'OK');

            $rowData = [
                $item->id,
                $item->name,
                ucfirst($item->category),
                ucfirst(str_replace('_', ' ', $item->status)),
                $item->quantity,
                $item->low_stock_threshold,
                $health,
                $item->attributes['location'] ?? '—',
                $item->updated_at->format('Y-m-d H:i'),
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

            // Stock Health column (G) conditional colour
            match ($health) {
                'OUT OF STOCK' => $sheet->getStyle("G{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFCF222E']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF0F0']],
                ]),
                'LOW STOCK' => $sheet->getStyle("G{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FF9A6700']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF8E1']],
                ]),
                default => $sheet->getStyle("G{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FF1A7F37']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE6FFED']],
                ]),
            };

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

            foreach (['A', 'E', 'F'] as $col) {
                $sheet->getStyle("{$col}2:{$col}{$lastRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        // ── Summary footer ─────────────────────────────────────
        $summaryRow = $lastRow + 2;
        $summaryData = [
            ['Snapshot taken:',    now()->format('M d, Y H:i')],
            ['Total Items:',       $lastRow - 1],
            ['Total Units:',       "=SUM(E2:E{$lastRow})"],
            ['Low Stock Items:',   "=COUNTIF(G2:G{$lastRow},\"LOW STOCK\")"],
            ['Out of Stock:',      "=COUNTIF(G2:G{$lastRow},\"OUT OF STOCK\")"],
        ];

        foreach ($summaryData as $i => $pair) {
            $r = $summaryRow + $i;
            $sheet->setCellValue("G{$r}", $pair[0]);
            $sheet->setCellValue("H{$r}", $pair[1]);
            $sheet->getStyle("G{$r}")->getFont()->setBold(true)->getColor()->setARGB('FF6E7681');
        }

        return $spreadsheet;
    }
}
