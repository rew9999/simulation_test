<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::get('/item/{id}', [ItemController::class, 'show'])->name('items.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/item/{id}/like', [ItemController::class, 'toggleLike'])->name('items.like');
    Route::post('/item/{id}/comment', [ItemController::class, 'storeComment'])->name('items.comment');

    Route::get('/sell', [ItemController::class, 'create'])->name('items.create');
    Route::post('/sell', [ItemController::class, 'store'])->name('items.store');

    Route::get('/purchase/{id}', [PurchaseController::class, 'show'])->name('purchase.show');
    Route::post('/purchase/{id}', [PurchaseController::class, 'store'])->name('purchase.store');
    Route::get('/purchase/success/{id}', [PurchaseController::class, 'success'])->name('purchase.success');
    Route::get('/purchase/address/{id}', [PurchaseController::class, 'editAddress'])->name('purchase.address');
    Route::post('/purchase/address/{id}', [PurchaseController::class, 'updateAddress'])->name('purchase.address.update');

    Route::get('/mypage', [UserController::class, 'show'])->name('mypage');
    Route::get('/mypage/profile', [UserController::class, 'edit'])->name('mypage.edit');
    Route::post('/mypage/profile', [UserController::class, 'update'])->name('mypage.update');
});
