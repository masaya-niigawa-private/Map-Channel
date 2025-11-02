<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionStoreRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Models\Tag;
use Illuminate\Http\Request;

final class QuestionsController extends Controller
{
  // GET /api/v1/questions?q=&tag=&sort=new|score|solved&per_page=20
  public function index(Request $req)
  {
    $q = trim((string) $req->query('q', ''));
    $tag = trim((string) $req->query('tag', ''));
    $sort = (string) $req->query('sort', 'new');  // new|score|solved
    $perPage = min(max((int) $req->query('per_page', 20), 1), 100);

    $query = Question::query()
      ->where('status', 'published')                  // ② 公開条件
      ->with(['tags:id,name'])                        // ⑥ N+1回避
      ->withCount(['answers'])                        // ⑥ N+1回避
      ->when($q !== '', function ($qq) use ($q) {      // ⑤ LIKE検索
        $qq->where(function ($w) use ($q) {
          $w->where('title', 'like', "%{$q}%")
            ->orWhere('body', 'like', "%{$q}%");
        });
      })
      ->when($tag !== '', function ($qq) use ($tag) {
        $qq->whereHas('tags', fn($t) => $t->where('name', $tag));
      });

    // ⑤ 暫定sortルール
    if ($sort === 'solved') {
      $query->orderByDesc('is_resolved')->orderByDesc('created_at');
    } elseif ($sort === 'score') {
      $query->orderByDesc('answers_count')->orderByDesc('views_count');
    } else {
      $query->orderByDesc('created_at'); // new
    }

    $paginator = $query->cursorPaginate($perPage)->withQueryString(); // ④ ページング＝cursorPaginate

    // ④ JSONレイアウト: data + meta（next_cursor/prev_cursor付き）
    return QuestionResource::collection($paginator)->additional([
      'meta' => [
        'q' => $q,
        'tag' => $tag,
        'sort' => $sort,
        'per_page' => $perPage,
        'next_cursor' => optional($paginator->nextCursor())->encode(),
        'prev_cursor' => optional($paginator->previousCursor())->encode(),
      ]
    ]);
  }

  // GET /api/v1/questions/{id}
  public function show($id)
  {
    $question = Question::query()
      ->where('status', 'published')                  // ② 公開条件
      ->with(['tags:id,name'])                        // ⑥ N+1回避
      ->withCount(['answers'])
      ->findOrFail($id);

    // 回答は published のみ、並びは ベスト→いいね→新着（仕様固定）
    $answers = $question->answers()
      ->where('status', 'published')                  // ② 公開条件
      ->orderByDesc('is_best')
      ->orderByDesc('upvotes_count')
      ->orderByDesc('created_at')
      ->get(['id', 'question_id', 'author_id', 'body', 'is_best', 'upvotes_count', 'status', 'created_at']);

    // ④ JSONレイアウト: {data, included:{answers:[]}}
    return (new QuestionResource($question))->additional([
      'included' => [
        'answers' => $answers->map(function ($a) {
          return [
            'id' => $a->id,
            'question_id' => $a->question_id,
            'author_id' => $a->author_id,
            'body' => $a->body,
            'is_best' => (bool) $a->is_best,
            'upvotes_count' => (int) $a->upvotes_count,
            'status' => $a->status,
            'created_at' => $a->created_at,
          ];
        })
      ]
    ]);
  }

  // POST /api/v1/questions
  public function store(QuestionStoreRequest $req)
  {
    // 認証は後回し：author_id は一旦 null（後でFirebaseに差し替え）
    $authorId = null;

    $q = new Question();
    $q->author_id = $authorId;
    $q->title = $req->title;
    $q->body = $req->body;
    $q->status = $req->input('status', 'published');   // draft|published
    $q->is_resolved = false;
    $q->save();

    // タグ（最大5・正規化） ③
    $tags = collect($req->input('tags', []))
      ->filter()
      ->map(fn($s) => mb_strtolower(trim((string) $s)))
      ->unique()
      ->take(5);

    if ($tags->isNotEmpty()) {
      $existing = Tag::query()->whereIn('name', $tags)->pluck('id', 'name'); // name=>id
      $newNames = $tags->diff($existing->keys());

      foreach ($newNames as $name) {
        $tag = Tag::create(['name' => $name]);
        $existing->put($name, $tag->id);
      }
      $q->tags()->sync($existing->values()->all());
    }

    return (new QuestionResource($q->load('tags:id,name')))
      ->response()
      ->setStatusCode(201);
  }
}
