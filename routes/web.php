<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::middleware('legacy.account')->group(function () {
    Route::get('/akun', [AccountController::class, 'show'])->name('account');
    Route::get('/transaksi', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transaksi', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transaksi/{transaction}/pembayaran', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/transaksi/{transaction}/pembayaran', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/kelola/pembayaran', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/kelola/pembayaran/{payment}/verifikasi', [PaymentController::class, 'confirm'])->name('payments.confirm');
    Route::get('/jadwal/baru', [ScheduleController::class, 'create'])->name('schedules.create');
    Route::post('/jadwal/baru', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
