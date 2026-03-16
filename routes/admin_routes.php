<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MainController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\SettingController;

Route::middleware(['auth', 'role:admin'])->group(function () {

Route::controller(MainController::class)->group(function(){
    Route::get('/', 'index')->name('.index');
});

Route::controller(AccountController::class)
    ->prefix('accounts')
    ->name('.accounts')
    ->group(function () {
    Route::get('/',        'index') ->name('.index');
    Route::get('/create',  'create')->name('.create');
    Route::post('/',       'store') ->name('.store');
});

Route::controller(DepartmentController::class)
    ->prefix('departments')
    ->name('.departments')
    ->group(function () {
        // Page
        Route::get('/', 'index')->name('.index');
 
        // AJAX — must be before /{department}
        Route::get('/stats',           'stats')         ->name('.stats');
        Route::get('/branches',        'branches')      ->name('.branches');
        Route::get('/head-candidates', 'headCandidates')->name('.heads');
        Route::get('/list',            'list')          ->name('.list');
 
        // CRUD
        Route::post('/',                'store')  ->name('.store');
        Route::patch('/{department}',   'update') ->name('.update');
        Route::delete('/{department}',  'destroy')->name('.destroy');
    });
 
Route::controller(PositionController::class)
    ->prefix('positions')
    ->name('.positions')
    ->group(function () {
        // Page
        Route::get('/', 'index')->name('.index');
 
        // AJAX — specific routes before /{position}
        Route::get('/stats',       'stats')       ->name('.stats');
        Route::get('/departments', 'departments') ->name('.departments');
        Route::get('/list',        'list')        ->name('.list');
 
        // CRUD
        Route::post('/',                'store')  ->name('.store');
        Route::patch('/{position}',     'update') ->name('.update');
        Route::delete('/{position}',    'destroy')->name('.destroy');
    });
 


Route::controller(BranchController::class)
    ->prefix('branches')
    ->name('.branches')
    ->group(function () {
        // Page
        Route::get('/', 'index')->name('.index');
 
        // AJAX — specific routes before /{branch}
        Route::get('/stats', 'stats')->name('.stats');
        Route::get('/list',  'list') ->name('.list');
 
        // CRUD
        Route::post('/',              'store')  ->name('.store');
        Route::patch('/{branch}',     'update') ->name('.update');
        Route::delete('/{branch}',    'destroy')->name('.destroy');
    });

    
Route::controller(SettingController::class)
    ->prefix('settings')
    ->name('.settings')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
});

});
