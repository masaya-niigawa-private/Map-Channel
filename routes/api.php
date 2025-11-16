<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\BoardsApiController as C;
use App\Http\Controllers\Api\QuestionsController;
use App\Http\Controllers\Api\AnswersController;

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

// 近くの座標を取得する
Route::get('/spots/nearby', [AdminController::class, 'getNearby']);

// 表示範囲のデータを取得する
Route::get('/spots/in-bounds', [AdminController::class, 'getSpotsInBounds']);

// 登録
Route::post('/spots/store', [AdminController::class, 'storeAPI']);

// コメント登録
Route::post('/posts/store', [AdminController::class, 'storePostAPI']);

// 更新
Route::match(['POST', 'PATCH'], '/spots/update/{id}', [AdminController::class, 'updateAPI']);

// 削除
Route::delete('/spots/{id}', [AdminController::class, 'destroyAPI']);

// usersテーブル作成（※Firebaseユーザー登録正常終了時）
Route::post('/storeUser', [AdminController::class, 'storeUserAPI']);

// -----------------------------------掲示板-----------------------------------
// urlの前に /v1 が着く
Route::prefix('v1')->group(function () {
    // 公開エンドポイント
    Route::get('/categories', [C::class, 'categories']);                // カテゴリ一覧
    Route::get('/boards', [C::class, 'boardsIndex']);                   // フィード（sort: latest|trending|favorite|all）
    Route::get('/boards/{id}', [C::class, 'boardShow']);                // 詳細
    Route::post('/boards/{id}/views', [C::class, 'boardViews']);        // 閲覧数インクリメント
    Route::get('/boards/{id}/comments', [C::class, 'commentsIndex']);   // コメント一覧（公開・ASC）

    // 認証必須（Firebase ID Token）
    Route::middleware('auth:firebase')->group(function () {
        Route::post('/boards', [C::class, 'boardStore']);               // 作成（multipart）
        Route::patch('/boards/{id}', [C::class, 'boardUpdate']);        // 更新（JSON）
        Route::delete('/boards/{id}', [C::class, 'boardDestroy']);      // 論理削除

        Route::post('/boards/{id}/favorite', [C::class, 'favoriteStore']);   // お気に入り
        Route::delete('/boards/{id}/favorite', [C::class, 'favoriteDestroy']); // 解除

        Route::post('/boards/{id}/comments', [C::class, 'commentStore']);    // コメント作成
        Route::delete('/comments/{comment_id}', [C::class, 'commentDestroy']); // コメント削除（論理）

        Route::post('/boards/{id}/report', [C::class, 'reportStore']);       // 通報
    });
});

// -----------------------------知恵袋------------------------------------
Route::prefix('v1')->group(function () {
    // 一覧・詳細（公開）
    Route::get('/questions', [QuestionsController::class, 'index']);
    Route::get('/questions/{id}', [QuestionsController::class, 'show']);

    // 作成系（認証は後でミドルウェアを噛ませる）
    Route::post('/questions', [QuestionsController::class, 'store']);
    Route::post('/questions/{id}/answers', [AnswersController::class, 'store']);

    Route::put('/questions/{id}', [QuestionsController::class, 'update']);
    Route::delete('/questions/{id}', [QuestionsController::class, 'destroy']);
    Route::put('/answers/{id}', [AnswersController::class, 'update']);
    Route::delete('/answers/{id}', [AnswersController::class, 'destroy']);

    Route::put('/answers/{id}/best', [AnswersController::class, 'markBest']);
});
