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


Route::post('/error', [\App\Http\Controllers\ErrorController::class, 'report']);

Route::get('/', [\App\Http\Controllers\WebController::class, 'index']);

Route::get('/getNews', [\App\Http\Controllers\ContentAPIController::class, 'getNews']);
Route::get('/getServers', [\App\Http\Controllers\ContentAPIController::class, 'getServers']);
Route::get('/getChangelogs', [\App\Http\Controllers\ContentAPIController::class, 'getChangelogs']);
Route::get('/getWorlds', [\App\Http\Controllers\ContentAPIController::class, 'getWorlds']);
Route::get('/worldOverview/{server}/{world}', [\App\Http\Controllers\ContentAPIController::class, 'worldOverview']);
Route::get('/worldExtendedData/{server}/{world}', [\App\Http\Controllers\ContentAPIController::class, 'worldExtendedData']);
Route::get('/allyBasicData/{server}/{world}/{ally}', [\App\Http\Controllers\AllyAPIController::class, 'allyBasicData']);
Route::get('/allyChartData/{server}/{world}/{ally}', [\App\Http\Controllers\AllyAPIController::class, 'allyChartData']);
Route::get('/playerBasicData/{server}/{world}/{player}', [\App\Http\Controllers\PlayerAPIController::class, 'playerBasicData']);
Route::get('/playerChartData/{server}/{world}/{player}', [\App\Http\Controllers\PlayerAPIController::class, 'playerChartData']);
Route::get('/playerWorldPopup/{world}/{player}', [\App\Http\Controllers\PlayerAPIController::class, 'playerWorldPopup']);
Route::get('/villageBasicData/{server}/{world}/{village}', [\App\Http\Controllers\VillageAPIController::class, 'villageBasicData']);
Route::get('/villageAllyDataXY/{server}/{world}/{x}/{y}', [\App\Http\Controllers\VillageAPIController::class, 'villageAllyDataXY']);

//Search
Route::post('basicSearch', [\App\Http\Controllers\SearchController::class, 'search']);
Route::post('extendedSearch', [\App\Http\Controllers\SearchController::class, 'extendedSearch']);


//Datatables
Route::get('/tables/worldAlly/{server}/{world}', [\App\Http\Controllers\DatatableController::class, 'worldAlly']);
Route::get('/tables/worldPlayer/{server}/{world}', [\App\Http\Controllers\DatatableController::class, 'worldPlayer']);
Route::get('/tables/worldHistoryAlly/{server}/{world}', [\App\Http\Controllers\DatatableController::class, 'worldAllyHist']);
Route::get('/tables/worldHistoryPlayer/{server}/{world}', [\App\Http\Controllers\DatatableController::class, 'worldPlayerHist']);
Route::get('/tables/worldConquer/{server}/{world}/{type}', [\App\Http\Controllers\ConquerController::class, 'worldConquer']);
Route::get('/tables/worldConquerDailyPlayer/{server}/{world}', [\App\Http\Controllers\ConquerController::class, 'worldConquerDailyPlayer']);
Route::get('/tables/worldConquerDailyAlly/{server}/{world}', [\App\Http\Controllers\ConquerController::class, 'worldConquerDailyAlly']);
Route::get('/tables/allyPlayer/{server}/{world}/{ally}', [\App\Http\Controllers\AllyAPIController::class, 'allyPlayer']);
Route::get('/tables/allyAllyHistory/{server}/{world}/{ally}', [\App\Http\Controllers\AllyAPIController::class, 'allyAllyHist']);
Route::get('/tables/allyConquer/{server}/{world}/{type}/{ally}', [\App\Http\Controllers\ConquerController::class, 'allyConquer']);
Route::get('/tables/allyAllyChange/{server}/{world}/{type}/{ally}', [\App\Http\Controllers\AllyChangeController::class, 'allyAllyChange']);
Route::get('/tables/playerVillage/{server}/{world}/{player}', [\App\Http\Controllers\PlayerAPIController::class, 'playerVillage']);
Route::get('/tables/playerPlayerHistory/{server}/{world}/{player}', [\App\Http\Controllers\PlayerAPIController::class, 'playerPlayerHistory']);
Route::get('/tables/playerConquer/{server}/{world}/{type}/{player}', [\App\Http\Controllers\ConquerController::class, 'playerConquer']);
Route::get('/tables/playerAllyChange/{server}/{world}/{type}/{player}', [\App\Http\Controllers\AllyChangeController::class, 'playerAllyChange']);
Route::get('/tables/villageConquer/{server}/{world}/{type}/{village}', [\App\Http\Controllers\ConquerController::class, 'villageConquer']);

//Select2
Route::get('/select/village/{world}', [\App\Http\Controllers\SelectInputController::class, 'getSelect2Village']);
Route::get('/select/player/{world}', [\App\Http\Controllers\SelectInputController::class, 'getSelect2Player']);
Route::get('/select/ally/{world}', [\App\Http\Controllers\SelectInputController::class, 'getSelect2Ally']);
Route::get('/select/playerTop/{world}', [\App\Http\Controllers\SelectInputController::class, 'getSelect2PlayerTop']);
Route::get('/select/allyTop/{world}', [\App\Http\Controllers\SelectInputController::class, 'getSelect2AllyTop']);

//Maps
Route::get('/maps/{server}/{world}/{type}-{id}-{width}-{height}.{ext}', [\App\Http\Controllers\MapController::class, 'getSizedOverviewMap']);
Route::get('/maps/{server}/{world}/{type}-{id}.{ext}', [\App\Http\Controllers\MapController::class, 'getOverviewMap']);
