<?php

use App\Http\Controllers\Api\PetaJabatanDashboardController;
use App\Http\Controllers\Api\SmartTriageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'role:super-admin|kepegawaian'])
    ->get('/intelligence/layanan-triage', [SmartTriageController::class, 'index'])
    ->name('api.intelligence.layanan-triage.index');

Route::middleware(['auth:sanctum', 'role:super-admin|kepegawaian|pimpinan'])
    ->get('/peta-jabatan/dashboard', [PetaJabatanDashboardController::class, 'index'])
    ->name('api.peta-jabatan.dashboard');

