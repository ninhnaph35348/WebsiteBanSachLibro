<?php

use App\Http\Controllers\AdminOrderDetailController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\StatisticsController;
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
use App\Http\Controllers\PostController;
use App\Http\Controllers\VnPayController;
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
    Route::post('reviews/', [ReviewController::class, 'store']);
    Route::delete('/products/{productCode}/reviews/{id}', [ReviewController::class, 'destroyReviewProduct']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/update-profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/order_detail', [OrderDetailController::class, 'index']);
    Route::get('/order_detail/{code_order}', [OrderDetailController::class, 'show']);
});
Route::middleware('auth:sanctum')->put('/me', [UserController::class, 'updateMe']);
Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return response()->json(['user' => $request->user()]);
});
// Quên mật khẩu
Route::post('forgot-password', [AuthController::class, 'sendResetLink']);
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
        Route::get('/search', [ProductController::class, 'search']);
        Route::get('/filter', [ProductController::class, 'product_filtering']);
        Route::get('/{id}', [ProductController::class, 'show']);
        Route::post('/', [ProductController::class, 'store']);
        Route::put('/edit/{id}', [ProductController::class, 'update']);
        Route::put('/update-status/{id}', [ProductController::class, 'updateProductStatus']);
        Route::put('/{id}', [ProductController::class, 'destroy']);
    });
    Route::prefix('product_variants')->group(function () {
        Route::get('/latest', [ProductController::class, 'latest']);
        Route::get('/', [ProductVariantController::class, 'index']);
        Route::get('/{id}', [ProductVariantController::class, 'show']);
        Route::get('/product-variant/{productCode}/cover/{coverId}', [ProductVariantController::class, 'getByProductAndCover']);
        Route::post('/', [ProductVariantController::class, 'store']);
        Route::put('/edit/{id}/cover/{coverId}', [ProductVariantController::class, 'update']);
        Route::put('/update-status/{code}/id/{id}', [ProductVariantController::class, 'updateProductVariantStatus']);
        Route::put('/{id}', [ProductVariantController::class, 'destroy']);
    });
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/order-detail/{code_order}', [AdminOrderDetailController::class, 'show']);
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
        Route::put('/edit/{id}', [ReviewController::class, 'update']);
        Route::delete('/{id}', [ReviewController::class, 'destroy']);
        Route::put('/{id}', [ReviewController::class, 'hidden']);
    });
    Route::get('status', [OrderStatusController::class, 'getAllOrderStatus']);
});
// Get All
Route::get('vouchers/', [VoucherController::class, 'index']);
Route::get('users/', [UserController::class, 'index']);
Route::get('categories/', [CategoryController::class, 'index']);
Route::get('covers/', [CoverController::class, 'index']);
Route::get('authors/', [AuthorController::class, 'index']);
Route::get('languages/', [LanguageController::class, 'index']);
Route::get('publishers/', [PublisherController::class, 'index']);
Route::get('genres/', [GenreController::class, 'index']);
Route::get('products_status/', [ProductController::class, 'getAllProductByStatus']);
Route::get('product_variants_status/', [ProductVariantController::class, 'getAllProductVariantByStatus']);
Route::get('orders/', [OrderController::class, 'index']);
Route::get('/review-products/{productCode}', [ReviewController::class, 'getReviewByProductId']);
Route::get('product_variants_toprate/', [ProductVariantController::class, 'getTop5ProductVarriantByRating']);
// Get Detail
Route::get('vouchers/{id}', [VoucherController::class, 'show']);
Route::get('users/{id}', [UserController::class, 'show']);
Route::get('categories/{id}', [CategoryController::class, 'show']);
Route::get('covers/{id}', [CoverController::class, 'show']);
Route::get('authors/{id}', [AuthorController::class, 'show']);
Route::get('languages/{id}', [LanguageController::class, 'show']);
Route::get('publishers/{id}', [PublisherController::class, 'show']);
Route::get('genres/{id}', [GenreController::class, 'show']);
Route::get('products/{id}', [ProductController::class, 'show']);
Route::get('product_variants/{id}', [ProductVariantController::class, 'show']);
Route::get('orders/{id}', [OrderController::class, 'show']);
Route::get('reviews/{id}', [ReviewController::class, 'show']);

// All
Route::middleware('optional-auth')->post('carts/order/checkout', [CartController::class, 'checkout']);
Route::middleware('auth:api')->post('/orders/cancel/{order_code}', [CartController::class, 'cancelOrder']);

Route::get('status', [OrderStatusController::class, 'getAllOrderStatus']);
Route::get('product_variants/latest', [ProductController::class, 'latest']);
Route::get('products/search', [ProductController::class, 'search']);
Route::get('products/filter', [ProductController::class, 'product_filtering']);


// Thống kê
Route::prefix('statistics')->group(function () {
    Route::get('total-books', [StatisticsController::class, 'getTotalBooks']);
    Route::get('sold-books', [StatisticsController::class, 'getSoldBooks']);
    Route::get('in-stock', [StatisticsController::class, 'getInStock']);
    Route::get('total-revenue', [StatisticsController::class, 'getTotalRevenue']);
    Route::get('revenue-by-period', [StatisticsController::class, 'getRevenueByPeriod']);
    Route::get('best-sellers', [StatisticsController::class, 'getBestSellers']);
    Route::get('customers', [StatisticsController::class, 'getCustomerCount']);
    Route::get('total-reviews', [StatisticsController::class, 'getTotalReviews']);
    Route::get('orders-by-status', [StatisticsController::class, 'getOrdersByStatus']);
});
Route::get('/product_variants/{code}/cover/{cover_id}', [ProductVariantController::class, 'getByProductAndCover']);
Route::get('/products-bestsellers', [ProductController::class, 'bestSellers']);
// Route::get('/order_detail/{code_order}', [OrderDetailController::class, 'show']);

// VnPay
Route::post('/vnpay-create', [VnPayController::class, 'createPayment']);
Route::get('/vnpay-return', [VnPayController::class, 'vnpayReturn']);
