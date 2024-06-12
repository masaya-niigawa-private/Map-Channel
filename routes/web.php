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

// Route::get('/', function () {
//     return view('welcome');
// });

//マップ初期表示
Route::get('/', [MapController::class,'showMap'] );

//喫煙スポット登録
Route::get('/form', [AdminController::class,'store'] )->name("form.post");
Route::post('/form', [AdminController::class,'store'] )->name("form.post");