<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\LoanRequestController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ReturnController;

Route::middleware('guest')->group(function () {

    // Register
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Login
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

});

Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard User
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Katalog Barang
    Route::get('/items', [ItemController::class, 'index'])
        ->name('items.index');

    // Detail Barang
    Route::get('/items/{item}', [ItemController::class, 'show'])
        ->name('items.show');

    // Data Kalender Ketersediaan Barang
    Route::get('/items/{item}/calendar-data', [ItemController::class, 'calendarData'])
        ->name('items.calendar-data');
    
    Route::post('/loan-cart/add', [CartController::class, 'add'])->name('loan-cart.add');
    Route::delete('/loan-cart/{item}', [CartController::class, 'remove'])->name('loan-cart.remove');

    Route::get('/loan-requests/create', [LoanRequestController::class, 'create'])->name('loan-requests.create');
    Route::post('/loan-requests', [LoanRequestController::class, 'store'])->name('loan-requests.store');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/history', [TransactionController::class, 'history'])->name('transactions.history');

    Route::get('/returns/{loanRequest}', [ReturnController::class, 'create'])->name('returns.create');
    Route::post('/returns/{loanRequest}', [ReturnController::class, 'store'])->name('returns.store');

});

/*
|--------------------------------------------------------------------------
| ADMIN MODULE
|--------------------------------------------------------------------------
| Dikerjakan oleh teman pada branch feature/admin-module.
| Aktifkan kembali saat mulai mengerjakan modul admin.
|--------------------------------------------------------------------------
*/

/*
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

    });
*/