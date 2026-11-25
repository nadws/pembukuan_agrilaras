<?php

use App\Http\Controllers\CashflowController;
use App\Http\Controllers\CoaWebhookController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// Change notices of the COA from the main application. Signed, so no login is used.
Route::post('/coa-webhook', CoaWebhookController::class)->name('coa.webhook');

Route::get('/cashflowUangMasukSetahun', [CashflowController::class, 'cashflowUangMasukSetahun'])->name('cashflowUangMasukSetahun');
