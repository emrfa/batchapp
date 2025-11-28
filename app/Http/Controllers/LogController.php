<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\Mixer;
use Carbon\Carbon;

class LogController extends Controller
{
    public function index(Request $request)
    {
        // Return view only - data is fetched via Alpine.js from /api/logs
        return view('logs.index');
    }
}