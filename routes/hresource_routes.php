<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MainController;
use App\Http\Controllers\HResource\EmployeeController;
use App\Http\Controllers\HResource\RequestController;
use App\Http\Controllers\HResource\TeamAttendanceController;
use App\Http\Controllers\HResource\TeamScheduleController;
use App\Http\Controllers\HResource\LoanManagementController;
use App\Http\Controllers\HResource\ReportController;
Route::middleware(['auth', 'role:hr,admin'])->group(function () {

Route::controller(EmployeeController::class)
    ->prefix('employees')
    ->name('.employees')
    ->group(function () {
        Route::get('/',                     'index')         ->name('.index');
        Route::get('/create',               'create')        ->name('.create');
        Route::post('/',                    'store')         ->name('.store');
        Route::get('/{employee}',           'show')          ->name('.show');
        Route::get('/{employee}/edit',      'edit')          ->name('.edit');
        Route::patch('/{employee}',         'update')        ->name('.update');
        Route::post('/{employee}/schedule', 'assignSchedule')->name('.schedule');
        Route::patch('/{employee}/toggle',  'toggleStatus')  ->name('.toggle');

        // AJAX only — still needed for list filters
        Route::get('/data/list',            'list')          ->name('.list');
    });


Route::controller(TeamScheduleController::class)
    ->prefix('team-schedule')
    ->name('.team_schedule')
    ->group(function () {
        // Page
        Route::get('/', 'index')->name('.index');
 
        // AJAX — Templates
        Route::get('/templates',              'templates') ->name('.templates');
        Route::post('/templates',             'store')     ->name('.templates.store');
        Route::patch('/templates/{template}', 'update')    ->name('.templates.update');
        Route::delete('/templates/{template}','destroy')   ->name('.templates.destroy');
 
        // AJAX — Assignments
        Route::get('/assignments', 'assignments') ->name('.assignments');
        Route::post('/assign',     'bulkAssign')  ->name('.assign');
    });

Route::controller(TeamAttendanceController::class)
    ->prefix('team-attendance')
    ->name('.team_attendance')
    ->group(function () {
        Route::get('/',               'index')       ->name('.index');
        Route::get('/employees',      'employees')   ->name('.employees');
        Route::get('/records',        'records')     ->name('.records');
        Route::post('/upsert',        'upsert')      ->name('.upsert');
        Route::post('/bulk-upsert',   'bulkUpsert')  ->name('.bulk-upsert');
    });

Route::controller(RequestController::class)
    ->prefix('requests')
    ->name('.requests')
    ->group(function () {
        // Page
        Route::get('/', 'index')->name('.index');
 
        // AJAX — Stats
        Route::get('/pending-counts', 'pendingCounts')->name('.counts');
 
        // AJAX — Tab data
        Route::get('/leave',    'leaveRequests')    ->name('.leave');
        Route::get('/overtime', 'overtimeRequests') ->name('.overtime');
        Route::get('/profile',  'profileRequests')  ->name('.profile');
        Route::get('/history',  'history')          ->name('.history');
 
        // Actions
        Route::patch('/leave/{leave}/approve',     'approveLeave')    ->name('.leave.approve');
        Route::patch('/leave/{leave}/reject',      'rejectLeave')     ->name('.leave.reject');
        Route::patch('/overtime/{overtime}/approve','approveOvertime') ->name('.overtime.approve');
        Route::patch('/overtime/{overtime}/reject', 'rejectOvertime') ->name('.overtime.reject');
        Route::patch('/profile/{profile}/approve', 'approveProfile')  ->name('.profile.approve');
        Route::patch('/profile/{profile}/reject',  'rejectProfile')   ->name('.profile.reject');
    });
 

Route::controller(LoanManagementController::class)
    ->prefix('loans')
    ->name('.loans')
    ->group(function () {
        // Page
        Route::get('/', 'index')->name('.index');

        // AJAX — Data  (must be before /{loan})
        Route::get('/stats',     'stats')     ->name('.stats');
        Route::get('/list',      'list')      ->name('.list');
        Route::get('/employees', 'employees') ->name('.employees');

        // CRUD
        Route::post('/',         'store')   ->name('.store');
        Route::get('/{loan}',    'show')    ->name('.show');
        Route::patch('/{loan}',  'update')  ->name('.update');
        Route::delete('/{loan}', 'destroy') ->name('.destroy');
    });

Route::controller(ReportController::class)
    ->prefix('reports')
    ->name('.reports')
    ->group(function () {
        // Page
        Route::get('/', 'index')->name('.index');
 
        // AJAX — Report data (specific routes before any potential wildcard)
        Route::get('/employee-masterlist', 'employeeMasterlist') ->name('.masterlist');
        Route::get('/dtr',                 'dtr')                ->name('.dtr');
        Route::get('/payroll-register',    'payrollRegister')    ->name('.payroll');
        Route::get('/loans',               'loans')              ->name('.loans');
    });
});