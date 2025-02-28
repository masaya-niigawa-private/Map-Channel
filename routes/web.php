<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\AuthController;


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

//ログイン
Route::get('/login', [AuthController::class,'showLoginform'] );
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');

//ユーザー登録
Route::get('/register', [AuthController::class, 'showRegisterForm']);
Route::post('/register', [AuthController::class, 'register'])->name('register');

//ログアウト
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// ダッシュボード（ログイン後のページ）
Route::get('/dashboard', function () {
    if (!session()->has('user_id')) {
        return redirect()->route('login.form');
    }
    return "ようこそ、" . session('user_name') . "さん";
})->name('dashboard');