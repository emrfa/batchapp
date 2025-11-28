<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BatchMonitoringApiService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'https://api-batchmonitoring.nalendev.com';
    }

    public function getDashboardSummary($filter = null)
    {
        try {
            $response = Http::get("{$this->baseUrl}/api/v1/dashboard/summary", [
                'Filter' => $filter,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('BatchMonitoring API Error (Dashboard): ' . $response->status() . ' - ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('BatchMonitoring API Exception (Dashboard): ' . $e->getMessage());
            return null;
        }
    }

    public function getReportData($mixer = null, $startDate = null, $endDate = null, $pageNumber = 1, $pageSize = 10)
    {
        try {
            $queryParams = array_filter([
                'Mixer' => $mixer,
                'StartDate' => $startDate,
                'EndDate' => $endDate,
                'PageNumber' => $pageNumber,
                'PageSize' => $pageSize,
            ], function($value) {
                return !is_null($value) && $value !== '';
            });

            $response = Http::get("{$this->baseUrl}/api/v1/report/report-data", $queryParams);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('BatchMonitoring API Error (Report): ' . $response->status() . ' - ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('BatchMonitoring API Exception (Report): ' . $e->getMessage());
            return null;
        }
    }
}
