<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Carbon\Carbon;

class ProductionReportExport implements WithMultipleSheets
{
    protected $reportData;
    protected $mixer;
    protected $startDate;
    protected $endDate;

    public function __construct($reportData, $mixer, $startDate, $endDate)
    {
        $this->reportData = $reportData;
        $this->mixer = $mixer;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function sheets(): array
    {
        $sheets = [];
        $title = $this->getTitle();

        // Sheet 1: Summary with ALL data
        $sheets[] = new ProductionReportSheet(
            $this->reportData,
            $this->mixer,
            $title,
            'Summary'
        );

        // Group batches by date
        $batchesByDate = collect($this->reportData['batches'] ?? [])
            ->groupBy(function ($batch) {
                return Carbon::parse($batch['batchTime'])->format('Y-m-d');
            })
            ->sortKeys();

        // Create a sheet for each date
        foreach ($batchesByDate as $date => $batches) {
            $sheets[] = new ProductionReportSheet(
                ['batches' => $batches->values()->toArray()],
                $this->mixer,
                $title,
                $date
            );
        }

        return $sheets;
    }

    private function getTitle(): string
    {
        $mainTitle = 'LAPORAN PEMAKAIAN BAHAN BAKU';
        
        if (strpos($this->mixer, 'CM3') !== false || strpos($this->mixer, 'Mixer CM3') !== false) {
            return $mainTitle . "\nMIXER 3 ADUKAN KAKI";
        } elseif (strpos($this->mixer, 'CM4') !== false || strpos($this->mixer, 'Mixer CM4') !== false) {
            return $mainTitle . "\nMIXER 4 ADUKAN KAKI";
        } elseif (strpos($this->mixer, 'FM5') !== false || strpos($this->mixer, 'Mixer FM5') !== false) {
            return $mainTitle . "\nADUKAN KEPALA";
        } else {
            return $mainTitle;
        }
    }
}
