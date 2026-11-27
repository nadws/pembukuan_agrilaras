<?php

use App\Http\Controllers\CashflowController;
use App\Http\Controllers\PenjualanTelurApiController;
use App\Http\Controllers\StokKeluarApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Stok keluar per kandang untuk JOMA. Dibaca saja, memakai token di STOK_API_TOKEN.
Route::get('/stok-keluar', StokKeluarApiController::class)->name('stok.keluar');

// Penjualan telur per nota untuk JOMA. Dibaca saja, memakai token di PENJUALAN_API_TOKEN.
Route::get('/penjualan-telur', PenjualanTelurApiController::class)->name('penjualan.telur');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/cashflowUangMasukSetahun', [CashflowController::class, 'cashflowUangMasukSetahun'])->name('cashflowUangMasukSetahun');
