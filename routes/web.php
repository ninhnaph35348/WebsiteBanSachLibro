<?php

use App\Http\Controllers\Mails\MailController;
use App\Mail\OrderMail;
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

Route::get('/orders/{code_order}', [MailController::class, 'show'])->name('orders.show');
Route::get('/orders/cancel/{code_order}', [MailController::class, 'cancelFromEmail'])->name('orders.cancel');
Route::middleware('optional-auth')->post('/orders/cancel/{order_code}', [MailController::class, 'cancelOrder']);
