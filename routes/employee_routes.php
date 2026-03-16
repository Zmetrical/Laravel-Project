<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MainController;
use App\Http\Controllers\Employee\LeaveController;
use App\Http\Controllers\Employee\LoanController;
use App\Http\Controllers\Employee\OvertimeController;
use App\Http\Controllers\Employee\PayrollController;
use App\Http\Controllers\Employee\ProfileController;
use App\Http\Controllers\Employee\ScheduleController;
use App\Http\Controllers\Employee\TimekeepingController;

Route::middleware(['auth', 'role:employee,admin'])->group(function () {

Route::controller(MainController::class)->group(function(){
    Route::get('/', 'index')->name('.index');
});


Route::controller(ProfileController::class)
    ->prefix('profile')
    ->name('.profile')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
        Route::get('/requests',    'store')  ->name('.requests.store');
});

Route::controller(ScheduleController::class)
    ->prefix('schedule')
    ->name('.schedule')
    ->group(function () {
        Route::get('/', 'index') ->name('.index');


});

Route::controller(TimekeepingController::class)
    ->prefix('timekeeping')
    ->name('.timekeeping')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
        Route::post('/clock-in', 'clockIn') ->name('.clock-in');
        Route::post('/clock-out', 'clockOut') ->name('.clock-out');
        Route::post('/test-mode', 'testMode') ->name('.test-mode');
        Route::delete('/delete-attendance', 'deleteAttendance') ->name('.delete-attendance');

});


Route::controller(OvertimeController::class)
    ->prefix('overtime')
    ->name('.overtime')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
        Route::post('/',        'store') ->name('.store');
        Route::get('/{overtimeRequest}',        'destroy') ->name('.destroy');

});

Route::controller(LeaveController::class)
    ->prefix('leave')
    ->name('.leave')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
        Route::post('/', 'store') ->name('.store');
        Route::delete('/{id}', 'destroy') ->name('.destroy');
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

});