<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FundTransferController;
use App\Http\Controllers\RuleController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Schedule
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');
    Route::patch('/schedule/{transaction}/status', [ScheduleController::class, 'updateStatus'])->name('schedule.update-status');

    // Transactions
    Route::resource('transactions', TransactionController::class)->except(['show']);
    Route::patch('/transactions/{transaction}/status', [TransactionController::class, 'updateStatus'])->name('transactions.update-status');

    // Rules
    Route::resource('rules', RuleController::class)->except(['show']);

    // Bank Accounts
    Route::resource('bank-accounts', BankAccountController::class)->except(['show']);
    Route::put('/bank-accounts/reorder', [BankAccountController::class, 'reorder'])->name('bank-accounts.reorder');

    // Fund Transfers
    Route::get('/fund-transfers', [FundTransferController::class, 'index'])->name('fund-transfers.index');
    Route::get('/fund-transfers/create', [FundTransferController::class, 'create'])->name('fund-transfers.create');
    Route::post('/fund-transfers', [FundTransferController::class, 'store'])->name('fund-transfers.store');
    Route::patch('/fund-transfers/{fundTransfer}/confirm', [FundTransferController::class, 'confirm'])->name('fund-transfers.confirm');
    Route::patch('/fund-transfers/{fundTransfer}/cancel', [FundTransferController::class, 'cancel'])->name('fund-transfers.cancel');

    // Batch Operations
    Route::post('/batch/generate', [BatchController::class, 'generate'])->name('batch.generate');
    Route::post('/batch/carry-over', [BatchController::class, 'carryOver'])->name('batch.carry-over');

    // Account Settings
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::patch('/account/email', [AccountController::class, 'updateEmail'])->name('account.update-email');
    Route::patch('/account/password', [AccountController::class, 'updatePassword'])->name('account.update-password');
});

require __DIR__.'/auth.php';
