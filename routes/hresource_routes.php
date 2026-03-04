<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MainController;
use App\Http\Controllers\HResource\EmployeeController;
use App\Http\Controllers\HResource\RequestController;
use App\Http\Controllers\HResource\TeamAttendanceController;
use App\Http\Controllers\HResource\TeamScheduleController;
use App\Http\Controllers\HResource\LoanManagementController;
use App\Http\Controllers\HResource\ReportController;

Route::controller(EmployeeController::class)
    ->prefix('employees')
    ->name('.employees')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
        Route::get('/create',        'create') ->name('.create');

});

Route::controller(TeamAttendanceController::class)
    ->prefix('team_attendance')
    ->name('.team_attendance')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
});

Route::controller(TeamScheduleController::class)
    ->prefix('team_schedule')
    ->name('.team_schedule')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
});

Route::controller(RequestController::class)
    ->prefix('requests')
    ->name('.requests')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
});


Route::controller(LoanManagementController::class)
    ->prefix('loans')
    ->name('.loans')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
});

Route::controller(ReportController::class)
    ->prefix('reports')
    ->name('.reports')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
});