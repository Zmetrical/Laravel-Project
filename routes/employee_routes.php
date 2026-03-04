<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MainController;
use App\Http\Controllers\employee\LeaveController;
use App\Http\Controllers\employee\LoanController;
use App\Http\Controllers\employee\OvertimeController;
use App\Http\Controllers\employee\PayrollController;
use App\Http\Controllers\Employee\ProfileController;
use App\Http\Controllers\employee\ScheduleController;
use App\Http\Controllers\employee\TimekeepingController;

Route::controller(MainController::class)->group(function(){
    Route::get('/', 'index')->name('.index');
});

Route::controller(ProfileController::class)
    ->prefix('profile')
    ->name('.profile')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
});

Route::controller(ScheduleController::class)
    ->prefix('schedule')
    ->name('.schedule')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
});

Route::controller(TimekeepingController::class)
    ->prefix('timekeeping')
    ->name('.timekeeping')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
});

Route::controller(OvertimeController::class)
    ->prefix('overtime')
    ->name('.overtime')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
});

Route::controller(LeaveController::class)
    ->prefix('leave')
    ->name('.leave')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
});


Route::controller(PayrollController::class)
    ->prefix('payroll')
    ->name('.payroll')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
});

Route::controller(LoanController::class)
    ->prefix('loan')
    ->name('.loan')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
});