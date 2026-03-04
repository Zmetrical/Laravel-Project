<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MainController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\SettingController;

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
        Route::get('/',        'index') ->name('.index');
});

Route::controller(PositionController::class)
    ->prefix('positions')
    ->name('.positions')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
});


Route::controller(BranchController::class)
    ->prefix('branches')
    ->name('.branches')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
});

Route::controller(SettingController::class)
    ->prefix('settings')
    ->name('.settings')
    ->group(function () {
        Route::get('/',        'index') ->name('.index');
});

