<?php



/*
|--------------------------------------------------------------------------
| API Routes — MBG Traceability System
|--------------------------------------------------------------------------
|
| Semua route di-prefix otomatis dengan /api oleh Laravel.
| Contoh: Route::get('suppliers', ...) → GET /api/suppliers
|
| ── TUGAS NASAR ──────────────────────────────────────────────────────────
|
| Tambahkan route berikut:
|
| // Supplier
| Route::apiResource('suppliers', SupplierController::class);
|
| // Bahan Makanan
| Route::apiResource('bahan-makanan', BahanMakananController::class);
|
| // Menu
| Route::apiResource('menu', MenuController::class);
| Route::post('menu/{id}/bahan', [DetailMenuController::class, 'store']);
|
| // Sekolah
| Route::apiResource('sekolah', SekolahController::class);
|
| // SPPG (Distribusi)
| Route::apiResource('sppg', SppgController::class);
|
| // Laporan Keracunan (MongoDB)
| Route::apiResource('laporan-keracunan', LaporanKeracunanController::class);
|
| // Traceability (Cross-DB)
| Route::get('trace/report/{id}',   [TraceabilityController::class, 'traceFromReport']);
| Route::get('trace/supplier/{id}', [TraceabilityController::class, 'traceFromSupplier']);
|
*/

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\BahanMakananController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\DetailMenuController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\SppgController;
use App\Http\Controllers\LaporanKeracunanController;
use App\Http\Controllers\TraceabilityController;

/*
|--------------------------------------------------------------------------
| API Routes — MBG Traceability System
|--------------------------------------------------------------------------
|
| Semua route di-prefix otomatis dengan /api oleh Laravel.
| Contoh: Route::get('suppliers', ...) → GET /api/suppliers
|
*/

// Supplier
Route::apiResource('suppliers', SupplierController::class);

// Bahan Makanan
Route::apiResource('bahan-makanan', BahanMakananController::class);

// Menu
Route::apiResource('menu', MenuController::class);
Route::post('menu/{id}/bahan', [DetailMenuController::class, 'store']);

// Sekolah
Route::apiResource('sekolah', SekolahController::class);

// SPPG (Distribusi)
Route::apiResource('sppg', SppgController::class);

// Laporan Keracunan (MongoDB)
Route::apiResource('laporan-keracunan', LaporanKeracunanController::class);

// Traceability (Cross-DB)
Route::get('trace/report/{id}',   [TraceabilityController::class, 'traceFromReport']);
Route::get('trace/supplier/{id}', [TraceabilityController::class, 'traceFromSupplier']);