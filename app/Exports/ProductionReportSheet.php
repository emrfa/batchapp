<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ProductionReportSheet implements FromView, WithTitle, ShouldAutoSize, WithStyles
{
    protected $reportData;
    protected $mixer;
    protected $title;
    protected $sheetName;

    public function __construct($reportData, $mixer, $title, $sheetName = 'Summary')
    {
        $this->reportData = $reportData;
        $this->mixer = $mixer;
        $this->title = $title;
        $this->sheetName = $sheetName;
    }

    public function view(): View
    {
        return view('reports.production-excel', [
            'reportData' => $this->reportData,
            'mixer' => $this->mixer,
            'title' => $this->title,
        ]);
    }

    public function title(): string
    {
        return $this->sheetName;
    }

    public function styles(Worksheet $sheet)
    {
        // Freeze the first rows (title + headers)
        $sheet->freezePane('A5');

        // Style title rows (bold, centered)
        $sheet->getStyle('A1:Z3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style header rows (bold, gray background, borders)
        $lastColumn = $this->getLastColumn();
        $sheet->getStyle("A4:{$lastColumn}5")->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Apply borders to all data cells
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A4:{$lastColumn}{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        return [];
    }

    private function getLastColumn(): string
    {
     
        if (strpos($this->mixer, 'FM5') !== false || strpos($this->mixer, 'Mixer FM5') !== false) {
            return 'K'; 
        } elseif (strpos($this->mixer, 'CM4') !== false || strpos($this->mixer, 'Mixer CM4') !== false) {
            return 'M'; 
        } else {
            return 'L'; 
        }
    }
}
