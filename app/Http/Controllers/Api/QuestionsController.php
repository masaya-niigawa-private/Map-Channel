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
    $q       = trim((string) $req->query('q', ''));
    $tag     = trim((string) $req->query('tag', ''));
    $sort    = (string) $req->query('sort', 'new');  // new|score|solved
    $perPage = min(max((int) $req->query('per_page', 20), 1), 100);
    $cursor  = $req->query('cursor');               // null可（そのまま渡す）

    $query = Question::query()
        ->where('status', 'published')          // ② 公開条件
        ->with(['tags:id,name'])                // ⑥ N+1回避
        ->withCount(['answers'])                // ⑥ N+1回避
        ->when($q !== '', function ($qq) use ($q) { // ⑤ LIKE検索
            $qq->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                  ->orWhere('body', 'like', "%{$q}%");
            });
        })
        ->when($tag !== '', function ($qq) use ($tag) {
            $qq->whereHas('tags', fn($t) => $t->where('name', $tag));
        });

    // ⑤ 暫定sortルール（cursor用に必ず tie-breaker: id desc を追加）
    if ($sort === 'solved') {
        $query->orderByDesc('is_resolved')
              ->orderByDesc('created_at')
              ->orderByDesc('id');
    } elseif ($sort === 'score') {
        $query->orderByDesc('answers_count')
              ->orderByDesc('views_count')
              ->orderByDesc('id');
    } else { // new
        $query->orderByDesc('created_at')
              ->orderByDesc('id');
    }

    // cursorPaginate（※ withQueryStringは不要。links/metaは使わないため）
    $paginator = $query->cursorPaginate($perPage, ['*'], 'cursor', $cursor);

    // ✅ Resourceに“paginator本体”は渡さない。itemsのみを整形して配列化
    $data = QuestionResource::collection(collect($paginator->items()))->resolve();

    // ✅ 手組みJSON：links/metaの自動付与を避け、クライアント仕様に揃える
    return response()->json([
        'data' => $data,
        'meta' => [
            'q'           => $q ?: null,
            'tag'         => $tag ?: null,
            'sort'        => $sort,
            'per_page'    => $perPage,                               // 数値
            'next_cursor' => $paginator->nextCursor()?->encode(),    // 文字列 or null
            'prev_cursor' => $paginator->previousCursor()?->encode(),// 文字列 or null（必要なければ削除可）
        ],
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
