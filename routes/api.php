<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CoverController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PublisherController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderDetailController;
use App\Http\Controllers\OrderStatusController;
use App\Models\User;

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

// Dang ky
Route::post('/register', [AuthController::class, 'register']);
// Đăng nhâp đăng xuất
Route::get('/login', [AuthController::class, 'login_'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/order_detail', [OrderDetailController::class, 'index']);
    Route::get('/order_detail/{code_order}', [OrderDetailController::class, 'show']);
});

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return response()->json(['user' => $request->user()]);
});

// Routes cho Super Admin (Toàn quyền, bao gồm quản lý users)
Route::middleware(['auth:sanctum',  'role:s.admin'])->group(function () {
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::post('/', [UserController::class, 'store']);
        Route::put('/edit/{id}', [UserController::class, 'update']);
        Route::put('/{id}', [UserController::class, 'destroy']);
    });
});
// Routes cho cả Admin và Super Admin (Quản lý tất cả trừ users)
Route::middleware(['auth:sanctum',  'role:s.admin|admin'])->group(function () {
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('/edit/{id}', [CategoryController::class, 'update']);
        Route::put('/{id}', [CategoryController::class, 'destroy']);
    });
    Route::prefix('authors')->group(function () {
        Route::get('/', [AuthorController::class, 'index']);
        Route::get('/{id}', [AuthorController::class, 'show']);
        Route::post('/', [AuthorController::class, 'store']);
        Route::put('/edit/{id}', [AuthorController::class, 'update']);
        Route::put('/{id}', [AuthorController::class, 'destroy']);
    });
    Route::prefix('covers')->group(function () {
        Route::get('/', [CoverController::class, 'index']);
        Route::get('/{id}', [CoverController::class, 'show']);
        Route::post('/', [CoverController::class, 'store']);
        Route::put('/edit/{id}', [CoverController::class, 'update']);
        Route::put('/{id}', [CoverController::class, 'destroy']);
    });
    Route::prefix('languages')->group(function () {
        Route::get('/', [LanguageController::class, 'index']);
        Route::get('/{id}', [LanguageController::class, 'show']);
        Route::post('/', [LanguageController::class, 'store']);
        Route::put('/edit/{id}', [LanguageController::class, 'update']);
        Route::put('/{id}', [LanguageController::class, 'destroy']);
    });
    Route::prefix('publishers')->group(function () {
        Route::get('/', [PublisherController::class, 'index']);
        Route::get('/{id}', [PublisherController::class, 'show']);
        Route::post('/', [PublisherController::class, 'store']);
        Route::put('/edit/{id}', [PublisherController::class, 'update']);
        Route::put('/{id}', [PublisherController::class, 'destroy']);
    });
    Route::prefix('genres')->group(function () {
        Route::get('/', [GenreController::class, 'index']);
        Route::get('/{id}', [GenreController::class, 'show']);
        Route::post('/', [GenreController::class, 'store']);
        Route::put('/edit/{id}', [GenreController::class, 'update']);
        Route::put('/{id}', [GenreController::class, 'destroy']);
    });
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/latest', [ProductController::class, 'latest']);
        Route::get('/search', [ProductController::class, 'search']);
        Route::get('/filter', [ProductController::class, 'product_filtering']);
        Route::get('/{id}', [ProductController::class, 'show']);
        Route::post('/', [ProductController::class, 'store']);
        Route::put('/edit/{id}', [ProductController::class, 'update']);
        Route::put('/{id}', [ProductController::class, 'destroy']);
    });
    Route::prefix('product_variants')->group(function () {
        Route::get('/', [ProductVariantController::class, 'index']);
        Route::get('/{id}', [ProductVariantController::class, 'show']);
        Route::post('/', [ProductVariantController::class, 'store']);
        Route::put('/edit/{id}', [ProductVariantController::class, 'update']);
        Route::put('/{id}', [ProductVariantController::class, 'destroy']);
    });
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{id}', [OrderController::class, 'show']);
        Route::put('/edit/{id}', [OrderController::class, 'update']);
    });
    Route::prefix('vouchers')->group(function () {
        Route::get('/', [VoucherController::class, 'index']);
        Route::get('/{id}', [VoucherController::class, 'show']);
        Route::post('/', [VoucherController::class, 'store']);
        Route::put('/edit/{id}', [VoucherController::class, 'update']);
        Route::put('/{id}', [VoucherController::class, 'destroy']);
    });
    Route::prefix('reviews')->group(function () {
        Route::get('/', [ReviewController::class, 'index']);
        Route::get('/{id}', [ReviewController::class, 'show']);
        Route::post('/', [ReviewController::class, 'store']);
        Route::put('/edit/{id}', [ReviewController::class, 'update']);
        Route::delete('/{id}', [ReviewController::class, 'destroy']);
        Route::put('/{id}', [ReviewController::class, 'hidden']);
    });
    Route::get('status', [OrderStatusController::class, 'getAllOrderStatus']);
});
// Get All
Route::get('users/', [UserController::class, 'index']);
Route::get('categories/', [CategoryController::class, 'index']);
Route::get('covers/', [CoverController::class, 'index']);
Route::get('authors/', [AuthorController::class, 'index']);
Route::get('languages/', [LanguageController::class, 'index']);
Route::get('publishers/', [PublisherController::class, 'index']);
Route::get('genres/', [GenreController::class, 'index']);
Route::get('products/', [ProductController::class, 'index']);
Route::get('product_variants/', [ProductVariantController::class, 'index']);
Route::get('orders/', [OrderController::class, 'index']);
Route::get('reviews/', [VoucherController::class, 'index']);
// Get Detail
Route::get('users/{id}', [UserController::class, 'show']);
Route::get('categories/{id}', [CategoryController::class, 'show']);
Route::get('covers/{id}', [AuthorController::class, 'show']);
Route::get('authors/{id}', [AuthorController::class, 'show']);
Route::get('languages/{id}', [LanguageController::class, 'show']);
Route::get('publishers/{id}', [PublisherController::class, 'show']);
Route::get('genres/{id}', [GenreController::class, 'show']);
Route::get('products/{id}', [ProductController::class, 'show']);
Route::get('product_variants/{id}', [ProductVariantController::class, 'show']);
Route::get('orders/{id}', [OrderController::class, 'show']);
Route::get('reviews/{id}', [VoucherController::class, 'show']);

// All 
Route::post('carts/order/checkout', [CartController::class, 'checkout']);
Route::get('orders/status', [OrderStatusController::class, 'getAllOrderStatus']);
Route::get('products/latest', [ProductController::class, 'latest']);
Route::get('products/search', [ProductController::class, 'search']);
Route::get('products/filter', [ProductController::class, 'product_filtering']);
