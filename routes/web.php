<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\MapController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//マップ初期表示
Route::get('/', [MapController::class,'showMap'] );

//スポット登録
Route::post('/form', [AdminController::class,'store'] );

//意見・要望送信
Route::post('/opinion', [AdminController::class,'opinion_submit'] );