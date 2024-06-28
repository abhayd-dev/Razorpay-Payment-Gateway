<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [PaymentController::class, 'showPaymentPage'])->name('payment.page');
Route::post('/create-order', [PaymentController::class, 'createOrder'])->name('create.order');
Route::post('/payment-success', [PaymentController::class, 'verifyPayment'])->name('payment.success');
Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
Route::get('/payments/get', [PaymentController::class, 'getPayments'])->name('payments.get');
Route::get('payment/status/{id}', [PaymentController::class, 'getPaymentStatus'])->name('payment.status');
Route::get('payment/invoice/{id}', [PaymentController::class, 'getPaymentInvoice'])->name('payment.invoice');
Route::post('/payment/{id}/markInvoiceDownloaded',
 [PaymentController::class, 'markInvoiceDownloaded'])->name('payment.markInvoiceDownloaded');


Route::get('/test', [PaymentController::class,'test']);






