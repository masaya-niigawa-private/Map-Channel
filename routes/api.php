<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//近くの座標を取得する
Route::get('/spots/nearby', [AdminController::class, 'getNearby']);

//表示範囲のデータを取得する
Route::get('/spots/in-bounds', [AdminController::class, 'getSpotsInBounds']);

//登録
Route::post('/spots/store', [AdminController::class, 'storeAPI']);