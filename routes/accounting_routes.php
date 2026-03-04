<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Accounting\PayrollManagementController;
use App\Http\Controllers\Accounting\SalaryManagementController;

Route::controller(PayrollManagementController::class)
    ->prefix('payroll')
    ->name('.payroll')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
});

Route::controller(SalaryManagementController::class)
    ->prefix('salary')
    ->name('.salary')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
});