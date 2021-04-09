<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\TimeBlockController;
use App\Http\Controllers\API\SettingsController;
use App\Http\Controllers\API\StatController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::apiResource('categories', CategoryController::class)->middleware('auth:api');
Route::apiResource('time-blocks', TimeBlockController::class)
        ->middleware('auth:api')->only('index', 'show', 'destroy', 'update');
Route::apiResource('categories/{category}/time-blocks', TimeBlockController::class)
        ->middleware('auth:api')->only('store');
Route::apiResource('stat', StatController::class)
        ->middleware('auth:api')->only('index');

Route::middleware('auth:api')->get('/settings', SettingsController::class . '@index');
Route::middleware('auth:api')->patch('/settings', SettingsController::class . '@update');