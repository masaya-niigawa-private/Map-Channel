<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnswerStoreRequest;
use App\Http\Resources\AnswerResource;
use App\Models\Answer;
use App\Models\Question;

final class AnswersController extends Controller
{
  // POST /api/v1/questions/{id}/answers
  public function store(AnswerStoreRequest $req, $id)
  {
    // hidden は受け付けない、published/draft の質問にのみ回答可（ここでは published のみに絞る）
    $question = Question::where('status', 'published')->findOrFail($id);

    $authorId = null; // 認証は後回し（後で $request->user()->id に差し替え）

    $ans = new Answer();
    $ans->question_id = $question->id;
    $ans->author_id = $authorId;
    $ans->body = $req->body;
    $ans->is_best = false;
    $ans->upvotes_count = 0;
    $ans->status = 'published';
    $ans->save();

    // ⑦ 回答投稿後、質問側のカウンタを即時反映
    $question->increment('answers_count');

    return (new AnswerResource($ans))
      ->response()
      ->setStatusCode(201);
  }
}
