<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\Mixer;
use App\Models\Material;
use App\Models\BatchDetail;
use App\Models\Storage;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductionReportExport;

class ReportController extends Controller
{
    protected $apiService;

    public function __construct(\App\Services\BatchMonitoringApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index(Request $request)
    {
        return view('reports.production');
    }

    public function productionData(Request $request)
    {
        $mixer = $request->query('mixer');
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');
        $pageNumber = $request->query('PageNumber', 1);
        $pageSize = $request->query('PageSize', 10);

        // Map Mixer Names to Codes
        $mixerMap = [
            'Mixer CM3' => 'cm3',
            'Mixer CM4' => 'cm4',
            'Mixer FM5' => 'fm5',
        ];

        // Handle "All Mixers" or empty string
        if (empty($mixer) || $mixer === 'All Mixers') {
            $mixer = null;
        } elseif (isset($mixerMap[$mixer])) {
            $mixer = $mixerMap[$mixer];
        }

        \Illuminate\Support\Facades\Log::info('Report API Request', ['mixer' => $mixer, 'start' => $startDate, 'end' => $endDate, 'PageNumber' => $pageNumber, 'PageSize' => $pageSize]);

        $reportData = $this->apiService->getReportData($mixer, $startDate, $endDate, $pageNumber, $pageSize);

        \Illuminate\Support\Facades\Log::info('Report API Response', ['data_count' => isset($reportData['data']) ? 'Yes' : 'No']);

        if (!$reportData || !isset($reportData['data'])) {
             // Fallback to mock if API fails
             return response()->json($this->getMockReportData());
        }

        $data = $reportData['data'];

        $batches = collect($data['reportTables'] ?? []);

        $semenTotal = $batches->sum(fn($row) => ($row['silo1'] ?? 0) + ($row['silo3'] ?? 0));
        $semenHCTotal = $batches->sum(fn($row) => ($row['silo2'] ?? 0));
        $pasirTotal = $batches->sum(fn($row) => ($row['aggregat2'] ?? 0) + ($row['aggregat3'] ?? 0) + ($row['aggregat4'] ?? 0));
        $airTotal = $batches->sum(fn($row) => ($row['water'] ?? 0));

        // FM5-specific totals
        $fm5SemenAbu = $batches->sum(fn($row) => ($row['silo1'] ?? 0));
        $fm5SemenPutih = $batches->sum(fn($row) => ($row['cementPutih'] ?? 0));
        $fm5SemenTotal = $fm5SemenAbu + $fm5SemenPutih;
        $fm5PasirGalunggung = $batches->sum(fn($row) => ($row['aggregat5'] ?? 0));
        $fm5PigmenQty = $batches->sum(fn($row) => ($row['pigmenQty'] ?? 0));

        return response()->json([
            'PageNumber' => (int)$pageNumber,
            'PageSize' => (int)$pageSize,
            'totalBatches' => $data['totalBatches'] ?? 0,
            'totalWeight' => $data['totalWeight'] ?? 0,
            'materialSummary' => $mixer === 'fm5' ? [
                ['label' => 'Semen', 'value' => $fm5SemenTotal, 'unit' => 'kg'],
                ['label' => 'Pasir beton', 'value' => $fm5PasirGalunggung, 'unit' => 'pulsa'], 
                ['label' => 'Pigmen', 'value' => $fm5PigmenQty, 'unit' => 'kg'], 
                ['label' => 'Air', 'value' => $airTotal, 'unit' => 'Liter'],
            ] : [
                ['label' => 'Semen', 'value' => $semenTotal, 'unit' => 'kg'],
                ['label' => 'Semen HC', 'value' => $semenHCTotal, 'unit' => 'kg'],
                ['label' => 'Pasir', 'value' => $pasirTotal, 'unit' => 'pulsa'],
                ['label' => 'Air', 'value' => $airTotal, 'unit' => 'Liter'],
            ],
            'mixers' => [
                ['mixerCode' => 'Mixer CM3'],
                ['mixerCode' => 'Mixer CM4'],
                ['mixerCode' => 'Mixer FM5']
            ],
            'materials' => [
                ['materialCode' => 'Semen (Kg)'],
                ['materialCode' => 'Semen HC (Kg)'],
                ['materialCode' => 'Pasir (Pulsa)'],
                ['materialCode' => 'Air']
            ],
            'storageMap' => [
                'Semen (Kg)' => ['Silo 1', 'Silo 3'],
                'Semen HC (Kg)' => ['Silo 2'],
                'Pasir (Pulsa)' => ['Ciloseh / Kuarsa', 'Giling 5 / Giling 6', 'Screening'],
                'Air' => null
            ],
            'batches' => $this->mapBatches($data['reportTables'] ?? [])
        ]);
    }

    private function mapBatches($reportTables)
    {
        return collect($reportTables)->map(function ($row) {
            return [
                'idBatch' => $row['batchId'],
                'batchTime' => $row['date'],
                'recipeCode' => $row['recipe'],
                'mixerCode' => $row['mixer'],
                'details' => $this->mapBatchDetails($row)
            ];
        })->toArray();
    }

    private function mapBatchDetails($row)
    {
        $details = [];
        
        // Semen (Kg) - Silo 1 & Silo 3
        if (isset($row['silo1']) && $row['silo1'] > 0) {
            $details[] = ['materialCode' => 'Semen (Kg)', 'storageCode' => 'Silo 1', 'quantity' => $row['silo1']];
        }
        if (isset($row['silo3']) && $row['silo3'] > 0) {
            $details[] = ['materialCode' => 'Semen (Kg)', 'storageCode' => 'Silo 3', 'quantity' => $row['silo3']];
        }

        // Semen HC (Kg) - Silo 2
        if (isset($row['silo2']) && $row['silo2'] > 0) {
            $details[] = ['materialCode' => 'Semen HC (Kg)', 'storageCode' => 'Silo 2', 'quantity' => $row['silo2']];
        }

        // Pasir (Pulsa)
        // Ciloseh / Kuarsa - aggregat2
        if (isset($row['aggregat2']) && $row['aggregat2'] > 0) {
            $details[] = ['materialCode' => 'Pasir (Pulsa)', 'storageCode' => 'Ciloseh / Kuarsa', 'quantity' => $row['aggregat2']];
        }
        // Giling 5 / Giling 6 - aggregat3
        if (isset($row['aggregat3']) && $row['aggregat3'] > 0) {
            $details[] = ['materialCode' => 'Pasir (Pulsa)', 'storageCode' => 'Giling 5 / Giling 6', 'quantity' => $row['aggregat3']];
        }
        // Screening - aggregat4
        if (isset($row['aggregat4']) && $row['aggregat4'] > 0) {
            $details[] = ['materialCode' => 'Pasir (Pulsa)', 'storageCode' => 'Screening', 'quantity' => $row['aggregat4']];
        }

        // Air - water
        if (isset($row['water']) && $row['water'] > 0) {
            $details[] = ['materialCode' => 'Air', 'storageCode' => null, 'quantity' => $row['water']];
        }

        // FM5-specific mappings
        // Abu -> silo1 (FM5 Semen Abu)
        if (isset($row['silo1']) && $row['silo1'] > 0) {
            $details[] = ['materialCode' => 'Semen FM5 Abu', 'storageCode' => 'Abu', 'quantity' => $row['silo1']];
        }
        
        // Putih -> cementPutih (FM5 Semen Putih)
        if (isset($row['cementPutih']) && $row['cementPutih'] > 0) {
            $details[] = ['materialCode' => 'Semen FM5 Putih', 'storageCode' => 'Putih', 'quantity' => $row['cementPutih']];
        }
        
        // Gallungung -> aggregat5 (FM5 Pasir beton)
        if (isset($row['aggregat5']) && $row['aggregat5'] > 0) {
            $details[] = ['materialCode' => 'Pasir FM5', 'storageCode' => 'Galunggung', 'quantity' => $row['aggregat5']];
        }
        
        // Warna -> pigmenColor (FM5 Pigmen Color)
        if (isset($row['pigmenColor']) && !empty($row['pigmenColor'])) {
            $details[] = ['materialCode' => 'Pigmen Warna', 'storageCode' => null, 'quantity' => $row['pigmenColor']];
        }
        
        // Qty -> pigmenQty (FM5 Pigmen Quantity)
        if (isset($row['pigmenQty']) && $row['pigmenQty'] > 0) {
            $details[] = ['materialCode' => 'Pigmen Qty', 'storageCode' => null, 'quantity' => $row['pigmenQty']];
        }

        return $details;
    }

    private function getMockReportData()
    {
        return [
            'totalBatches' => 45,
            'totalWeight' => 150000,
            'materialSummary' => [
                ['label' => 'Semen', 'value' => 30000, 'unit' => 'kg'],
                ['label' => 'Semen HC', 'value' => 20000, 'unit' => 'kg'],
                ['label' => 'Pasir', 'value' => 90000, 'unit' => 'pulsa'],
                ['label' => 'Air', 'value' => 10000, 'unit' => 'Liter'],
            ],
            'mixers' => [
                ['mixerCode' => 'Mixer A'],
                ['mixerCode' => 'Mixer B']
            ],
            'materials' => [
                ['materialCode' => 'Cement'],
                ['materialCode' => 'Sand'],
                ['materialCode' => 'Gravel'],
                ['materialCode' => 'Water']
            ],
            'storageMap' => [
                'Cement' => ['Silo 1', 'Silo 2'],
                'Sand' => ['Bin 1'],
                'Gravel' => ['Bin 2'],
                'Water' => ['Tank 1']
            ],
            'batches' => [
                [
                    'idBatch' => 'B-1001',
                    'batchTime' => now()->subMinutes(10)->toIso8601String(),
                    'recipeCode' => 'REC-001',
                    'mixerCode' => 'Mixer A',
                    'details' => [
                        ['materialCode' => 'Cement', 'storageCode' => 'Silo 1', 'quantity' => 500],
                        ['materialCode' => 'Sand', 'storageCode' => 'Bin 1', 'quantity' => 800],
                        ['materialCode' => 'Gravel', 'storageCode' => 'Bin 2', 'quantity' => 1000],
                        ['materialCode' => 'Water', 'storageCode' => 'Tank 1', 'quantity' => 200]
                    ]
                ]
            ]
        ];
    }

    public function exportExcel(Request $request)
    {
        $mixer = $request->query('mixer');
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        // Map Mixer Names to Codes
        $mixerMap = [
            'Mixer CM3' => 'cm3',
            'Mixer CM4' => 'cm4',
            'Mixer FM5' => 'fm5',
        ];

        $mixerDisplay = $mixer;
        
        // Handle "All Mixers" or empty string
        if (empty($mixer) || $mixer === 'All Mixers') {
            $mixerCode = null;
            $mixerDisplay = 'All_Mixers';
        } elseif (isset($mixerMap[$mixer])) {
            $mixerCode = $mixerMap[$mixer];
        } else {
            $mixerCode = $mixer;
        }

        // First, check total count to prevent memory issues
        $countData = $this->apiService->getReportData($mixerCode, $startDate, $endDate, 1, 1);
        $totalRecords = $countData['data']['totalBatches'] ?? 0;

        // Safety limit to prevent memory exhaustion
        if ($totalRecords > 1000) {
            return response()->json([
                'error' => 'Too many records to export',
                'message' => "Found {$totalRecords} records. Please narrow your date range or filter by a specific mixer. Maximum allowed: 1000 records."
            ], 400);
        }

        // Fetch ALL data (up to 1000 records)
        $reportData = $this->apiService->getReportData($mixerCode, $startDate, $endDate, 1, 1000);

        if (!$reportData || !isset($reportData['data'])) {
            return response()->json(['error' => 'Failed to fetch report data'], 500);
        }

        $data = $reportData['data'];
        
        // Map batches using existing method
        $exportData = [
            'batches' => $this->mapBatches($data['reportTables'] ?? [])
        ];

        // Generate filename
        $filename = 'Production_Report_' . 
                    str_replace(' ', '_', $mixerDisplay) . '_' . 
                    $startDate . '_to_' . $endDate . '.xlsx';

        try {
            return Excel::download(
                new ProductionReportExport($exportData, $mixer, $startDate, $endDate),
                $filename
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Excel Export Error: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('Error in file: ' . $e->getFile() . ' on line ' . $e->getLine());
            \Illuminate\Support\Facades\Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Export failed',
                'message' => 'An error occurred while generating the Excel file. Please try a smaller date range.'
            ], 500);
        }
    }
}