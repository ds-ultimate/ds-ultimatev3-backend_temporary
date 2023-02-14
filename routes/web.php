<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [\App\Http\Controllers\WebController::class, 'index']);

Route::get('/indexPage', [\App\Http\Controllers\ContentAPIController::class, 'index']);
Route::get('/serverGetWorlds/{server}', [\App\Http\Controllers\ContentAPIController::class, 'serverGetWorlds']);
Route::get('/worldOverview/{server}/{world}', [\App\Http\Controllers\ContentAPIController::class, 'worldOverview']);

//Datatables
Route::get('/tables/worldAlly/{server}/{world}', [\App\Http\Controllers\DatatableController::class, 'worldAlly']);
Route::get('/tables/worldPlayer/{server}/{world}', [\App\Http\Controllers\DatatableController::class, 'worldPlayer']);
