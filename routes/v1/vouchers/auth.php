<?php

use App\Http\Controllers\Vouchers\DeleteVoucherHandler;
use App\Http\Controllers\Vouchers\GetVouchersHandler;
use App\Http\Controllers\Vouchers\GetVouchersTotalAmountHandler;
use App\Http\Controllers\Vouchers\RegularizeVoucherHandler;
use App\Http\Controllers\Vouchers\StoreVouchersHandler;
use Illuminate\Support\Facades\Route;

Route::prefix('vouchers')->group(
    function () {
        Route::get('/', GetVouchersHandler::class);
        Route::post('/', StoreVouchersHandler::class);
        Route::put('/', RegularizeVoucherHandler::class);
        Route::get('/total-amounts', GetVouchersTotalAmountHandler::class);
        Route::delete('/', DeleteVoucherHandler::class);
    }
);
