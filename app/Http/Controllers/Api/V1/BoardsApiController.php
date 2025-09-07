<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Category;
use App\Models\Board;
use App\Models\BoardFavorite;
use App\Models\BoardComment;
use App\Models\BoardReport;
use App\Models\User;

class BoardsApiController extends Controller
{
  /** GET /categories */
  public function categories()
  {
    // sort_order ASC（nullは末尾）
    $list = Category::query()
      ->orderByRaw('COALESCE(sort_order, 999999), id')
      ->get()
      ->map(fn($c) => [
        'id' => (int) $c->id,
        'name' => (string) $c->name,
        'sort_order' => $c->sort_order === null ? null : (int) $c->sort_order,
      ])
      ->values();

    return response()->json($list, 200);
  }

  /** GET /boards  (feed) */
  public function boardsIndex(Request $request)
  {
    $validated = $request->validate([
      'category_id' => ['nullable', 'integer', 'exists:categories,id'],
      'sort' => ['nullable', Rule::in(['latest', 'trending', 'favorite', 'all'])],
      'page' => ['nullable', 'integer', 'min:1'],
      'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
    ]);

    $sort = $validated['sort'] ?? 'latest';
    $page = (int) ($validated['page'] ?? 1);
    $perPage = (int) ($validated['per_page'] ?? 20);
    $user = Auth::user();

    // sort=favorite かつ未認証→401（仕様）:contentReference[oaicite:6]{index=6}
    if ($sort === 'favorite' && !$user) {
      return response()->json(['message' => 'Unauthorized'], 401);
    }

    $q = Board::query()
      ->with([
        'author:id,uid,userName',      // users（AuthorUser: id/uid/name=userName）:contentReference[oaicite:7]{index=7}
        'category:id,name,sort_order'
      ])
      ->when(
        isset($validated['category_id']),
        fn($qq) => $qq->where('category_id', $validated['category_id'])
      );

    // 並び順（trendingは favorite_count → view_count → created_at）
    if ($sort === 'latest' || $sort === 'all') {
      $q->orderByDesc('created_at');
    } elseif ($sort === 'trending') {
      $q->orderByDesc('favorite_count')->orderByDesc('view_count')->orderByDesc('created_at');
    } elseif ($sort === 'favorite') {
      $q->whereIn('boards.id', function ($sub) use ($user) {
        $sub->select('board_id')->from('board_favorites')->where('user_id', $user->id);
      })->orderByDesc('created_at');
    }

    $p = $q->paginate($perPage, ['*'], 'page', $page);

    $data = collect($p->items())->map(fn(Board $b) => $this->boardResource($b))->values();

    return response()->json([
      'data' => $data,
      'meta' => $this->paginateMeta($p),
    ], 200);
  }

  /** GET /boards/{id} */
  public function boardShow(int $id)
  {
    $b = Board::with(['author:id,uid,userName', 'category:id,name,sort_order'])->find($id);
    if (!$b)
      return response()->json(['message' => 'Not Found'], 404);

    return response()->json($this->boardResource($b), 200);
  }

  /** POST /boards  (multipart) */
  public function boardStore(Request $request)
  {
    $u = Auth::user();

    $v = $request->validate([
      'category_id' => ['required', 'integer', 'exists:categories,id'],
      'description' => ['required', 'string', 'max:150'],
      'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
      'location_name' => ['nullable', 'string'],
      'location_lat' => ['nullable', 'numeric', 'between:-90,90'],
      'location_lng' => ['nullable', 'numeric', 'between:-180,180'],
      'link_url' => ['nullable', 'url', 'max:255'],
    ]); // リクエスト定義に準拠（multipart）:contentReference[oaicite:8]{index=8}

    $photoPath = null;
    if ($request->hasFile('photo')) {
      $photoPath = $request->file('photo')->store('boards', ['disk' => config('filesystems.default')]);
    }

    $b = new Board();
    $b->author_id = $u->id;                 // boards.author_id（FK→users.id）:contentReference[oaicite:9]{index=9}
    $b->category_id = $v['category_id'];
    $b->photo_path = $photoPath;             // 物理はphoto_path、返却はphoto_urlにマッピング
    $b->location_name = $v['location_name'] ?? null;
    $b->location_lat = $v['location_lat'] ?? null;
    $b->location_lng = $v['location_lng'] ?? null;
    $b->description = $v['description'];
    $b->link_url = $v['link_url'] ?? null;
    $b->view_count = 0;                      // 内部管理項目:contentReference[oaicite:10]{index=10}
    $b->favorite_count = 0;
    $b->save();

    // 201でBoardスキーマ返却（photo_url, location構造など）:contentReference[oaicite:11]{index=11}
    return response()->json($this->boardResource($b->load(['author', 'category'])), 201);
  }

  /** PATCH /boards/{id} */
  public function boardUpdate(int $id, Request $request)
  {
    $u = Auth::user();
    $b = Board::find($id);
    if (!$b)
      return response()->json(['message' => 'Not Found'], 404);
    if ((int) $b->author_id !== (int) $u->id) {
      return response()->json(['message' => 'Forbidden'], 403);   // 作者のみ更新可:contentReference[oaicite:12]{index=12}
    }

    $v = $request->validate([
      'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
      'description' => ['sometimes', 'string', 'max:150'],
      'location_name' => ['sometimes', 'nullable', 'string'],
      'location_lat' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
      'location_lng' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
      'link_url' => ['sometimes', 'nullable', 'url', 'max:255'],
    ]); // JSONの一部更新に一致:contentReference[oaicite:13]{index=13}

    $b->fill($v)->save();

    return response()->json($this->boardResource($b->fresh(['author', 'category'])), 200);
  }

  /** DELETE /boards/{id} (soft delete) */
  public function boardDestroy(int $id)
  {
    $u = Auth::user();
    $b = Board::find($id);
    if (!$b)
      return response()->json(['message' => 'Not Found'], 404);
    if ((int) $b->author_id !== (int) $u->id) {
      return response()->json(['message' => 'Forbidden'], 403);
    }

    $b->delete(); // boardsのみSoftDeletes対象:contentReference[oaicite:14]{index=14}
    return response()->noContent(); // 204:contentReference[oaicite:15]{index=15}
  }

  /** POST /boards/{id}/views  -> {view_count} */
  public function boardViews(int $id)
  {
    $b = Board::find($id);
    if (!$b)
      return response()->json(['message' => 'Not Found'], 404);

    $b->increment('view_count');                // 規定の戻りスキーマに適合:contentReference[oaicite:16]{index=16}
    $b->refresh();

    return response()->json(['view_count' => (int) $b->view_count], 200);
  }

  /** POST /boards/{id}/favorite  /  DELETE /boards/{id}/favorite */
  public function favoriteStore(int $id)
  {
    $u = Auth::user();
    $b = Board::find($id);
    if (!$b)
      return response()->json(['message' => 'Not Found'], 404);

    BoardFavorite::firstOrCreate(['board_id' => $b->id, 'user_id' => $u->id]); // UNIQUE(board_id,user_id):contentReference[oaicite:17]{index=17}
    $count = BoardFavorite::where('board_id', $b->id)->count();
    $b->update(['favorite_count' => $count]);

    return response()->json([
      'favorite_count' => (int) $b->favorite_count,
      'is_favorited' => true,
    ], 200); // スキーマ準拠:contentReference[oaicite:18]{index=18}
  }

  public function favoriteDestroy(int $id)
  {
    $u = Auth::user();
    $b = Board::find($id);
    if (!$b)
      return response()->json(['message' => 'Not Found'], 404);

    BoardFavorite::where('board_id', $b->id)->where('user_id', $u->id)->delete();
    $count = BoardFavorite::where('board_id', $b->id)->count();
    $b->update(['favorite_count' => $count]);

    return response()->json([
      'favorite_count' => (int) $b->favorite_count,
      'is_favorited' => false,
    ], 200); // スキーマ準拠（DELETEも同スキーマ）:contentReference[oaicite:19]{index=19}
  }

  /** GET /boards/{id}/comments  (ASC, paged, excludes soft-deleted) */
  public function commentsIndex(int $id, Request $request)
  {
    $b = Board::find($id);
    if (!$b)
      return response()->json(['message' => 'Not Found'], 404);

    $page = (int) $request->query('page', 1);
    $perPage = (int) min((int) $request->query('per_page', 20), 50);

    $q = BoardComment::query()
      ->where('board_id', $b->id)
      ->orderBy('created_at', 'asc');        // ASC/ソフトデリート除外（モデルSoftDeletes想定）:contentReference[oaicite:20]{index=20}

    $p = $q->paginate($perPage, ['*'], 'page', $page);

    $data = collect($p->items())->map(fn(BoardComment $c) => $this->commentResource($c))->values();

    return response()->json([
      'data' => $data,
      'meta' => $this->paginateMeta($p),
    ], 200);
  }

  /** POST /boards/{id}/comments */
  public function commentStore(int $id, Request $request)
  {
    $u = Auth::user();
    $b = Board::find($id);
    if (!$b)
      return response()->json(['message' => 'Not Found'], 404);

    $v = $request->validate([
      'content' => ['required', 'string', 'max:500'],
    ]); // 定義に一致（必須/500文字）:contentReference[oaicite:21]{index=21}

    $c = new BoardComment();
    $c->board_id = $b->id;
    $c->author_id = $u->id;                    // board_comments.author_id:contentReference[oaicite:22]{index=22}
    $c->content = $v['content'];
    $c->save();

    return response()->json($this->commentResource($c->fresh(['author'])), 201);
  }

  /** DELETE /comments/{comment_id}  (soft delete) */
  public function commentDestroy(int $comment_id)
  {
    $u = Auth::user();
    $c = BoardComment::find($comment_id);
    if (!$c)
      return response()->json(['message' => 'Not Found'], 404);
    if ((int) $c->author_id !== (int) $u->id) {
      return response()->json(['message' => 'Forbidden'], 403);
    }

    $c->delete(); // board_commentsのみSoftDeletes対象（ver4条件）:contentReference[oaicite:23]{index=23}
    return response()->noContent(); // 204:contentReference[oaicite:24]{index=24}
  }

  /** POST /boards/{id}/report */
  public function reportStore(int $id, Request $request)
  {
    $u = Auth::user();
    $b = Board::find($id);
    if (!$b)
      return response()->json(['message' => 'Not Found'], 404);

    $v = $request->validate([
      'reason' => ['nullable', 'string', 'max:255'],
    ]); // 任意項目・255字:contentReference[oaicite:25]{index=25}

    $r = new BoardReport();
    $r->board_id = $b->id;
    $r->reporter_id = $u->id;                   // reporter_id（テーブル設計）:contentReference[oaicite:26]{index=26}
    $r->reason = $v['reason'] ?? null;
    $r->save();

    return response()->json(['status' => 'ok'], 200);
  }

  /* ====================== Resource builders ====================== */

  private function boardResource(Board $b): array
  {
    $author = $b->author instanceof User ? $b->author : $b->author()->first();
    $cat = $b->category ?: $b->category()->first();

    $isFavorited = false;
    if ($u = Auth::user()) {
      $isFavorited = BoardFavorite::where('board_id', $b->id)->where('user_id', $u->id)->exists();
    }

    return [
      'id' => (int) $b->id,
      'author' => [
        'id' => (int) $author->id,
        'uid' => $author->uid ?? null,
        'name' => (string) ($author->userName ?? $author->name),
      ],                                                  // AuthorUser:contentReference[oaicite:27]{index=27}
      'category' => [
        'id' => (int) $cat->id,
        'name' => (string) $cat->name,
        'sort_order' => $cat->sort_order === null ? null : (int) $cat->sort_order,
      ],
      'description' => (string) $b->description,
      'photo_url' => $b->photo_path ? Storage::url($b->photo_path) : null, // 返却キーはphoto_url:contentReference[oaicite:28]{index=28}
      'location' => [
        'name' => $b->location_name ?: null,
        'lat' => $b->location_lat === null ? null : (float) $b->location_lat,
        'lng' => $b->location_lng === null ? null : (float) $b->location_lng,
      ],
      'link_url' => $b->link_url ?: null,
      'view_count' => (int) ($b->view_count ?? 0),
      'favorite_count' => (int) ($b->favorite_count ?? 0),
      'is_favorited' => (bool) $isFavorited,
      'created_at' => $b->created_at?->toISOString(),
      'updated_at' => $b->updated_at?->toISOString(),
    ]; // Boardスキーマに整合（required等）:contentReference[oaicite:29]{index=29}
  }

  private function commentResource(BoardComment $c): array
  {
    $author = $c->author instanceof User ? $c->author : $c->author()->first();
    return [
      'id' => (int) $c->id,
      'board_id' => (int) $c->board_id,
      'author' => [
        'id' => (int) $author->id,
        'uid' => $author->uid ?? null,
        'name' => (string) ($author->userName ?? $author->name),
      ],
      'content' => (string) $c->content,
      'created_at' => $c->created_at?->toISOString(),
      'updated_at' => $c->updated_at?->toISOString(),
    ]; // Commentスキーマに整合:contentReference[oaicite:30]{index=30}
  }

  private function paginateMeta($p): array
  {
    return [
      'page' => (int) $p->currentPage(),
      'per_page' => (int) $p->perPage(),
      'total' => (int) $p->total(),
    ]; // PagedBoards/PagedCommentsのmeta構造に準拠:contentReference[oaicite:31]{index=31}
  }
}
