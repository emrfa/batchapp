<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BatchMonitoringApiService;

class ReceivingController extends Controller
{
    protected $apiService;

    public function __construct(BatchMonitoringApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index()
    {
        return view('receiving');
    }

    public function getTransactions()
    {
        $result = $this->apiService->getReceivingTransactions();

        if (!$result || !isset($result['data'])) {
            // Fallback to empty array if API fails
            return response()->json([
                'data' => []
            ]);
        }

        return response()->json($result);
    }

    public function getStorageList()
    {
        $result = $this->apiService->getStorageList();

        if (!$result || !isset($result['data'])) {
            // Fallback to empty array if API fails
            return response()->json([
                'data' => []
            ]);
        }

        return response()->json($result);
    }

    public function submitTransaction(Request $request)
    {
        $data = $request->validate([
            'receivedTime' => 'required|date',
            'idInventory' => 'required|integer',
            'supplier' => 'required|string',
            'documentRef' => 'required|string',
            'quantity' => 'required|numeric',
        ]);

        $result = $this->apiService->submitReceivingTransaction($data);

        if ($result) {
            return response()->json(['success' => true, 'message' => 'Stock received successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to submit transaction'], 500);
    }
}
