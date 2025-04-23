<?php

use App\Http\Controllers\Mails\MailController;
use App\Mail\OrderMail;
use App\Mail\OrderShipped;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/test-mail', function () {
    $order = (object)[
        'id' => 1234,
        'total' => 999000,
        'user' => (object)['email' => 'youremail@example.com'] // thay bằng email Chủ Nhân
    ];

    Mail::to($order->user->email)->send(new OrderMail($order));

    return 'Mail đã được gửi thành công cho Chủ Nhân!';
});
Route::get('/orders/{code_order}', [MailController::class, 'show'])->name('orders.show');

