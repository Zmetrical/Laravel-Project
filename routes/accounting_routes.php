<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Accounting\SalaryManagementController;
use App\Http\Controllers\Accounting\PayrollPeriodController;
use App\Http\Controllers\Accounting\PayrollProcessController;
use App\Http\Controllers\Accounting\PayrollRecordsController;
Route::middleware(['auth', 'role:accounting,admin'])->group(function () {


Route::controller(SalaryManagementController::class)
    ->prefix('salary')
    ->name('.salary')
    ->group(function () {
        Route::get('/',          'index')      ->name('.index');
        Route::get('/list',      'list')       ->name('.list');        // AJAX table
        Route::post('/bulk',     'bulkUpdate') ->name('.bulk-update'); // AJAX bulk
        Route::get('/{user}',    'show')       ->name('.show');        // AJAX details
        Route::patch('/{user}',  'update')     ->name('.update');      // AJAX edit
    });
    // ── Payroll Periods ──────────────────────────────────────────────────
    Route::controller(PayrollPeriodController::class)
        ->prefix('payroll/periods')
        ->name('.payroll.periods')
        ->group(function () {
            Route::get('/',                   'index')       ->name('.index');
            Route::post('/',                  'store')       ->name('.store');
            Route::patch('/{period}/status',  'updateStatus')->name('.update-status');
        });

    // ── Process Payroll ──────────────────────────────────────────────────
    Route::controller(PayrollProcessController::class)
        ->prefix('payroll/periods/{period}/process')
        ->name('.payroll.periods')
        ->group(function () {
            Route::get('/',                     'show')         ->name('.process');
            Route::post('/save-all',    'saveAll')    ->name('.process.save-all');
            Route::post('/release-all', 'releaseAll') ->name('.process.release-all');
            Route::get('/{employee}/data',      'employeeData') ->name('.process.employee-data');
            Route::post('/{employee}/save',     'saveRecord')   ->name('.process.save');
        });

    // ── Payroll Records & Summary ────────────────────────────────────────────────
    Route::controller(PayrollRecordsController::class)
        ->prefix('payroll/periods/{period}')
        ->name('.payroll.periods')
        ->group(function () {
            Route::get('/records', 'index')   ->name('.records');
            Route::get('/summary', 'summary') ->name('.summary');
        });
        
});