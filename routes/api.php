<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PublisherController;
use App\Http\Controllers\VoucherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


// Đăng nhâp đăng xuất
Route::get('/login', [AuthController::class, 'login_'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);       // Lấy danh sách đơn hàng
    Route::get('/{id}', [OrderController::class, 'show']);    // Lấy chi tiết đơn hàng
    Route::post('/', [OrderController::class, 'store']);      // Tạo đơn hàng
    Route::put('/{id}', [OrderController::class, 'update']);  // Cập nhật đơn hàng
    Route::delete('/{id}', [OrderController::class, 'destroy']); // Xóa đơn hàng
});
//
Route::apiResource('orders', OrderController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('authors', AuthorController::class);
Route::apiResource('languages', LanguageController::class);
Route::apiResource('publishers', PublisherController::class);
Route::apiResource('genres', GenreController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('users', UserController::class);
Route::apiResource('vouchers', VoucherController::class);
Route::apiResource('product_variants', ProductVariantController::class);
