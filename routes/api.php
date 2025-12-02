<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReceivingController;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Dashboard Stats
Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

// Receiving Logs
Route::get('/receiving/recent', [ReceivingController::class, 'getTransactions']);
Route::get('/receiving/storage-list', [ReceivingController::class, 'getStorageList']);


// Submit Receiving
Route::post('/receiving/submit', [ReceivingController::class, 'submitTransaction']);

// Production Logs
Route::get('/logs', function () {
    $search = request('search');
    $startDate = request('startDate');
    $endDate = request('endDate');

    $logs = [
        [
            'id' => 101,
            'idBatch' => 'B-1001',
            'batchTime' => now()->subMinutes(10)->toIso8601String(),
            'recipeCode' => 'REC-001',
            'recipe' => ['name' => 'Standard Concrete'],
            'mixerCode' => 'Mixer A',
            'mixTime' => 120,
            'unloadTime' => 30,
            'totalWeight' => 5000
        ],
        [
            'id' => 102,
            'idBatch' => 'B-1002',
            'batchTime' => now()->subMinutes(25)->toIso8601String(),
            'recipeCode' => 'REC-002',
            'recipe' => ['name' => 'High Strength'],
            'mixerCode' => 'Mixer B',
            'mixTime' => 150,
            'unloadTime' => 0, // In progress
            'totalWeight' => 4800
        ],
            [
            'id' => 103,
            'idBatch' => 'B-1003',
            'batchTime' => now()->subMinutes(40)->toIso8601String(),
            'recipeCode' => 'REC-001',
            'recipe' => ['name' => 'Standard Concrete'],
            'mixerCode' => 'Mixer A',
            'mixTime' => 120,
            'unloadTime' => 32,
            'totalWeight' => 5100
        ]
    ];

    // Filter by Search
    if ($search) {
        $logs = array_filter($logs, function ($log) use ($search) {
            return stripos($log['idBatch'], $search) !== false || 
                    stripos($log['recipeCode'], $search) !== false ||
                    stripos($log['recipe']['name'], $search) !== false;
        });
    }

    // Filter by Date Range
    if ($startDate) {
        $logs = array_filter($logs, function ($log) use ($startDate) {
            return Carbon::parse($log['batchTime'])->startOfDay()->gte(Carbon::parse($startDate)->startOfDay());
        });
    }

    if ($endDate) {
        $logs = array_filter($logs, function ($log) use ($endDate) {
            return Carbon::parse($log['batchTime'])->endOfDay()->lte(Carbon::parse($endDate)->endOfDay());
        });
    }

    return response()->json([
        'data' => array_values($logs),
        'pagination' => [
            'current_page' => 1,
            'last_page' => 1,
            'prev_page_url' => null,
            'next_page_url' => null
        ]
    ]);
});

// Production Report
Route::get('/reports/production', [ReportController::class, 'productionData']);
Route::get('/reports/production/export', [ReportController::class, 'exportExcel']);
