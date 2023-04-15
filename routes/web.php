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
Route::get('/tables/worldHistoryAlly/{server}/{world}', [\App\Http\Controllers\DatatableController::class, 'worldAllyHist']);
Route::get('/tables/worldHistoryPlayer/{server}/{world}', [\App\Http\Controllers\DatatableController::class, 'worldPlayerHist']);
Route::get('/tables/worldConquer/{server}/{world}/{type}', [\App\Http\Controllers\ConquerController::class, 'worldConquer']);

//Select2
Route::get('/select/village/{world}', [\App\Http\Controllers\SelectInputController::class, 'getSelect2Village']);
Route::get('/select/player/{world}', [\App\Http\Controllers\SelectInputController::class, 'getSelect2Player']);
Route::get('/select/ally/{world}', [\App\Http\Controllers\SelectInputController::class, 'getSelect2Ally']);
Route::get('/select/playerTop/{world}', [\App\Http\Controllers\SelectInputController::class, 'getSelect2PlayerTop']);
Route::get('/select/allyTop/{world}', [\App\Http\Controllers\SelectInputController::class, 'getSelect2AllyTop']);
