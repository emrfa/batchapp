<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LogController;

use Carbon\Carbon;

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');





});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/reports/production', [ReportController::class, 'index'])->name('reports.production');
Route::get('/logs', [LogController::class, 'index'])->name('logs.index');

Route::get('/receiving', function () {
    $logs = [

        (object)[
            'time' => now()->subHours(2),
            'silo' => 'Silo 1 (Semen)', 
            'supplier' => 'Holcim Indonesia', 
            'ticket' => 'DO-2218', 
            'qty' => 25000
        ],
        (object)[
            'time' => now()->subHours(5),
            'silo' => 'Silo 1A (Semen)',    
            'supplier' => 'Local Quarry A', 
            'ticket' => 'T-9921',  
            'qty' => 12500
        ],

        (object)[
            'time' => now()->subDay()->setHour(14)->setMinute(30),
            'silo' => 'Silo 2 (Semen)',    
            'supplier' => 'Semen Padang',   
            'ticket' => 'DO-1102', 
            'qty' => 8000
        ],

        (object)[
            'time' => now()->subDays(2)->setHour(9)->setMinute(15),
            'silo' => 'Silo 2A (Semen)', 
            'supplier' => 'StoneGroup',  
            'ticket' => 'SG-881',  
            'qty' => 22000
        ],
    ];

    return view('receiving', compact('logs'));
})->name('receiving');

require __DIR__.'/auth.php';


