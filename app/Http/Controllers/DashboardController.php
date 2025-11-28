<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\Mixer;
use App\Models\BatchDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $apiService;

    public function __construct(\App\Services\BatchMonitoringApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index()
    {
        return view('dashboard');
    }

    public function stats(Request $request)
    {
        $filter = $request->query('filter', 'today'); // Default to 'today'
        $summary = $this->apiService->getDashboardSummary($filter);

        if (!$summary || !isset($summary['data'])) {
            // Fallback to mock if API fails
            return response()->json($this->getMockStats());
        }

        $data = $summary['data'];

        // Map API response to frontend structure
        return response()->json([
            'todaysCount' => $data['totalBatches'] ?? 0,
            'yesterdaysCount' => 10000, // Mocked
            'weeklyCount' => 8500, // Mocked
            'monthlyCount' => 35000, // Mocked
            'yearlyCount' => 420000, // Mocked
            'avgCycleTime' => $data['averageCycleTime'] ?? 0,
            'totalVolumeTons' => $data['totalVolume'] ?? 0,
            'mixers' => $this->getMockMixers($data['activeMixers'] ?? 0),
            'silos' => $this->mapSilos($data['inventoryLevels'] ?? []),
            'chart' => $this->mapChart($data['materialConsumptions'] ?? [])
        ]);
    }

    private function getMockMixers($activeCount)
    {
        // Generate mock mixers based on active count from API
        $mixers = [
            ['id' => 1, 'name' => 'Mixer CM3', 'mixerCode' => 'CM3', 'status' => 'RUNNING', 'current_recipe' => 'Concrete-X', 'today_count' => 45, 'availability' => 98, 'progress_percent' => 65],
            ['id' => 2, 'name' => 'Mixer CM4', 'mixerCode' => 'CM4', 'status' => 'STOPPED', 'current_recipe' => '-', 'today_count' => 30, 'availability' => 95, 'progress_percent' => 40],
            ['id' => 3, 'name' => 'Mixer FM5', 'mixerCode' => 'FM5', 'status' => 'MAINTENANCE', 'current_recipe' => '-', 'today_count' => 0, 'availability' => 0, 'progress_percent' => 0],
        ];

        return $mixers;
    }

    private function mapSilos($inventoryLevels)
    {
        return collect($inventoryLevels)->map(function ($item, $index) {
            $capacity = 100000;
            $percent = ($item['stock'] / $capacity) * 100;
            return [
                'id' => $index + 1,
                'name' => $item['name'],
                'material' => $item['name'],
                'unit' => 'kg',
                'percent' => round($percent, 1),
                'capacity' => $capacity,
                'current_quantity' => $item['stock']
            ];
        })->toArray();
    }

    private function mapChart($materialConsumptions)
    {
        $labels = [];
        $data = [];
        $units = [];

        foreach ($materialConsumptions as $item) {
            $labels[] = $item['name'];
            $data[] = $item['total'];
            
            $nameLower = strtolower($item['name']);
            if (str_contains($nameLower, 'pasir') ||  str_contains($nameLower, 'screening')) {
                $units[] = 'pulsa';
            } elseif (str_contains($nameLower, 'air')) {
                $units[] = 'Liter';
            } else {
                $units[] = 'kg';
            }
        }

        return [
            'labels' => $labels,
            'units' => $units,
            'data' => $data
        ];
    }

    private function getMockStats()
    {
        return [
            'todaysCount' => 1250,
            'yesterdaysCount' => 1115,
            'weeklyCount' => 8500,
            'monthlyCount' => 35000,
            'yearlyCount' => 420000,
            'avgCycleTime' => 120,
            'totalVolumeTons' => 4500,
            'mixers' => $this->getMockMixers(2),
            'silos' => [
                ['id' => 1, 'name' => 'Silo 1', 'material' => 'Semen', 'unit' => 'kg', 'percent' => 75, 'capacity' => 100000, 'current_quantity' => 75000],
                ['id' => 2, 'name' => 'Silo 2', 'material' => 'Pasir', 'unit' => 'pulsa', 'percent' => 45, 'capacity' => 80000, 'current_quantity' => 36000],
            ],
            'chart' => [
                'labels' => ['Semen', 'Pasir', 'Pigmen', 'Air'],
                'units' => ['kg', 'pulsa', 'kg', 'Liter'],
                'data' => [45000, 62000, 3800, 12000]
            ]
        ];
    }
}