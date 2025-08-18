<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Spot;
use App\Models\Comment;
use App\Models\Opinion;
use App\Models\Photo;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    // スポット登録（web用）
    public function store(Request $request)
    {
        // バリデーションルールを定義
        $validationRules = [
            'ido' => 'required',
            'keido' => 'required',
            'spot_name' => 'required',
            'evaluation' => 'required'
        ];
        // バリデーションチェック
        $request->validate($validationRules);

        try {
            $spot = new Spot();
            $spot->ido = $request['ido'];
            $spot->keido = $request['keido'];
            $spot->category = $request['category'];
            $spot->spot_name = $request['spot_name'];
            $spot->evaluation = $request['evaluation'];
            $spot->user_name = $request['user_name'];
            $spot->save();

            if ($request->hasFile('photo')) {
                foreach ($request->file('photo') as $file) {
                    if ($file) {
                        $photo = new Photo();
                        $photo->spot_id = $spot->id;
                        // S3にアップロード
                        $path = $file->store('photo', 's3');
                        $photo->photo_path = $path;
                        $photo->save();
                    }
                }
            }

            $comment = $request['comment'];
            if ($comment) {
                $comment = new Comment();
                $comment->spot_id = $spot->id;
                $comment->comment = $request['comment'];
                $comment->save();
            }
            // 登録成功時にリダイレクト
            return redirect()->back()->with('message', '正常に登録されました。');
        } catch (\Exception $e) {
            // 例外発生時にエラーメッセージを表示
            return back()->with('error', '登録に失敗しました。' . $e->getMessage());
        }
    }

    // スポット登録（API）
    public function storeAPI(Request $request)
    {
        // ---- 1) バリデーション（JSONで422返却） ----
        $validator = Validator::make($request->all(), [
            'ido' => ['required'],
            'keido' => ['required'],
            'spot_name' => ['required'],
            'evaluation' => ['required'],
            // 必要に応じて強化してください（例：numeric/between, image mimes など）
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // ---- 2) 登録（例外時は500をJSONで返却） ----
        try {
            DB::beginTransaction();

            $spot = new Spot();
            $spot->ido = $request->input('ido');
            $spot->keido = $request->input('keido');
            $spot->category = $request->input('category');
            $spot->spot_name = $request->input('spot_name');
            $spot->evaluation = $request->input('evaluation');
            $spot->user_name = $request->input('user_name');
            $spot->save();

            // 写真：単数/複数どちらでも対応
            if ($request->hasFile('photo')) {
                $files = Arr::wrap($request->file('photo'));
                foreach ($files as $file) {
                    if (!$file)
                        continue;
                    $photo = new Photo();
                    $photo->spot_id = $spot->id;
                    // S3にアップロード（元処理を踏襲）
                    $path = $file->store('photo', 's3');
                    $photo->photo_path = $path;
                    $photo->save();
                }
            }

            // コメント（任意）
            $commentInput = $request->input('comment');
            if (!empty($commentInput)) {
                $comment = new Comment();
                $comment->spot_id = $spot->id;
                $comment->comment = $commentInput;
                $comment->save();
            }

            DB::commit();

            // 必要最低限のレスポンス（SwiftUIで扱いやすい形に）
            $response = [
                'id' => $spot->id,
                'spot_name' => $spot->spot_name,
                'category' => $spot->category,
                'ido' => $spot->ido,
                'keido' => $spot->keido,
                'evaluation' => $spot->evaluation,
                'user_name' => $spot->user_name,
                'message' => '正常に登録されました。',
            ];

            // Locationヘッダー（必要なら）
            return response()
                ->json($response, 201)
                ->header('Location', url("/api/spots/{$spot->id}"));

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => '登録に失敗しました。',
                'error' => $e->getMessage(), // 運用で外すならここは消してください
            ], 500);
        }
    }

    //spotテーブルから全データ取得
    public function get()
    {
        $all_spots = Spot::all();
        $all_spots_json = json_encode($all_spots);
        return $all_spots_json;
    }

    // 範囲のみのデータを取得（spot,photo,comment,post）
    public function getSpotsInBounds(Request $request)
    {
        $validated = $request->validate([
            'swlat' => 'required|numeric|between:-90,90',   // 南西(左下)の緯度
            'swlng' => 'required|numeric|between:-180,180', // 南西(左下)の経度
            'nelat' => 'required|numeric|between:-90,90',   // 北東(右上)の緯度
            'nelng' => 'required|numeric|between:-180,180', // 北東(右上)の経度
            'limit' => 'nullable|integer|min:1|max:2000',
        ]);

        $limit = $validated['limit'] ?? 500;

        $query = Spot::query()
            ->whereBetween('ido', [$validated['swlat'], $validated['nelat']]);

        // 経度：日付変更線を跨ぐケースを考慮
        if ($validated['swlng'] <= $validated['nelng']) {
            // 通常ケース
            $query->whereBetween('keido', [$validated['swlng'], $validated['nelng']]);
        } else {
            // 例: swlng=170, nelng=-170 のように 180/-180 を跨ぐ場合
            $query->where(function ($q) use ($validated) {
                $q->where('keido', '>=', $validated['swlng'])
                    ->orWhere('keido', '<=', $validated['nelng']);
            });
        }

        // 関連データをまとめて取得（必要な列だけに絞ると軽量）
        $spots = $query
            ->with([
                'photos:id,spot_id,photo_path',
                'comments:id,spot_id,comment',
                'posts:id,spot_id,author,content'
            ])
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'ido', 'keido', 'category', 'spot_name', 'evaluation', 'user_name', 'created_at']); // Spot の列も必要最小限に

        return response()->json($spots);
    }

    //spotテーブルから全データ取得
    public function getKandai()
    {
        $kandai_spots = Spot::where('category', '関大')->get();
        $kandai_spots_json = json_encode($kandai_spots);
        return $kandai_spots_json;
    }

    //意見・要望をDBに保存
    public function opinion_submit(Request $request)
    {
        $validationRules = [
            'opinion' => 'required'
        ];
        $request->validate($validationRules);
        try {
            $opinion = new Opinion();
            $opinion->opinion = $request['opinion'];
            // $opinion->$request['category'];
            $opinion->save();
            return redirect()->back()->with('message', '送信されました。');
        } catch (\Exception $e) {
            // 例外発生時にエラーメッセージを表示
            return back()->with('error', '送信に失敗しました。' . $e->getMessage());
        }
    }

    //commentsテーブル検索
    public function getComments($id)
    {
        $comments = Comment::where('spot_id', $id)->get();
        return response()->json($comments);
    }

    //photsテーブル検索
    public function getPhotos($id)
    {
        $photos = Photo::where('spot_id', $id)->get();
        return response()->json($photos);
    }

    //更新機能（修正ボタン）
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'spot_name' => 'required|string|max:255',
            'evaluation' => 'required|string|max:255',
            'user_name' => 'nullable|string|max:255',
            'created_at' => 'nullable|date',
            'comment' => 'nullable|string|max:1000',
        ]);

        // spot_idをキーにレコード取得
        $spot = Spot::find($request['id']);
        $comment = Comment::where('spot_id', $request['id'])->first();

        if ($spot) {
            if (isset($validatedData['spot_name'])) {
                $spot->spot_name = $validatedData['spot_name'];
            }
            if (isset($validatedData['evaluation'])) {
                $spot->evaluation = $validatedData['evaluation'];
            }
            if (isset($validatedData['created_at'])) {
                $spot->created_at = $validatedData['created_at'];
            }
            if (isset($validatedData['user_name'])) {
                $spot->user_name = $validatedData['user_name'];
            }
            $spot->save();
        }

        if ($comment) {
            if (isset($validatedData['comment'])) {
                $comment->comment = $validatedData['comment'];
                $comment->save();
            }
        }
        return response()->json([
            'message' => '正常に修正されました。',
            'redirect_url' => url()->previous(),
        ]);

    }

    // 更新機能（API）
    public function updateAPI(Request $request, $id)
    {
        $v = Validator::make($request->all(), [
            // テキスト（存在したときだけ検証）
            'spot_name' => ['sometimes', 'string', 'max:255'],
            'name' => ['sometimes', 'string', 'max:255'], // 別名（互換）
            'evaluation' => ['sometimes'],                    // "1"〜"5"（文字列も許容）
            'rating' => ['sometimes'],
            'user_name' => ['sometimes', 'string', 'max:255'],
            'created_at' => ['sometimes', 'date'],

            // ▼ 画像（配列）＋ HEIC/HEIF を許可
            'photo' => ['sometimes', 'array'],
            'photo.*' => ['file', 'mimetypes:image/heic,image/heif,image/heic-sequence,image/heif-sequence,image/jpeg,image/png,image/webp', 'max:20480'],

            // ▼ 既存写真の削除ID（配列）
            'delete_photo_ids' => ['sometimes', 'array'],
            'delete_photo_ids.*' => ['integer'],
        ]);

        if ($v->fails()) {
            return response()->json(['message' => 'validation_error', 'errors' => $v->errors()], 422);
        }

        $spot = Spot::find($id);
        if (!$spot) {
            return response()->json(['message' => 'not_found'], 404);
        }

        DB::beginTransaction();
        try {
            // ------- テキスト部分（明示代入で安全に） -------
            if ($request->has('spot_name') || $request->has('name')) {
                $spot->spot_name = $request->input('spot_name', $request->input('name'));
            }
            if ($request->has('evaluation') || $request->has('rating')) {
                $spot->evaluation = (string) $request->input('evaluation', $request->input('rating'));
            }
            if ($request->has('user_name')) {
                $spot->user_name = $request->input('user_name');
            }
            if ($request->has('created_at')) {
                $spot->created_at = $request->input('created_at');
            }
            $spot->save();

            // ------- 既存写真の削除 -------
            $deleteIDs = array_filter(array_map('intval', (array) $request->input('delete_photo_ids', [])));
            if (!empty($deleteIDs)) {
                $photos = Photo::where('spot_id', $spot->id)->whereIn('id', $deleteIDs)->get();
                foreach ($photos as $p) {
                    // S3 から削除（photo_path は "photo/<filename>"）
                    if ($p->photo_path && Storage::disk('s3')->exists($p->photo_path)) {
                        Storage::disk('s3')->delete($p->photo_path);
                    }
                    $p->delete();
                }
            }

            // ------- 新規写真の追加（単数/複数） -------
            if ($request->hasFile('photo')) {
                foreach (Arr::wrap($request->file('photo')) as $file) {
                    if (!$file || !$file->isValid())
                        continue;

                    // S3 に "photo/<filename>" で保存 -> 返り値も "photo/xxx" になる
                    $path = $file->store('photo', 's3');

                    Photo::create([
                        'spot_id' => $spot->id,
                        'photo_path' => $path,   // 例: "photo/20250818_xxx.jpg"
                    ]);
                }
            }

            DB::commit();

            // 返却はDB確定値で
            $spot->load('photos:id,spot_id,photo_path');

            return response()->json([
                'message' => 'updated',
                'spot' => [
                    'id' => $spot->id,
                    'spot_name' => $spot->spot_name,
                    'evaluation' => $spot->evaluation,
                    'user_name' => $spot->user_name,
                    'created_at' => $spot->created_at,
                    'updated_at' => $spot->updated_at,
                ],
                'photos' => $spot->photos, // photo_path は "photo/<filename>"
                'comment' => null,
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['message' => 'error'], 500);
        }
    }

    public function deletePhoto($id)
    {
        try {
            // レコード取得
            $photo = Photo::findOrFail($id);

            // S3から画像削除
            if ($photo->photo_path) {
                $s3Path = $photo->photo_path;
                if (Storage::disk('s3')->exists($s3Path)) {
                    Storage::disk('s3')->delete($s3Path);
                }
            }

            // DBからレコード削除
            $photo->delete();

            return response()->json(['message' => '画像を削除しました。'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => '削除に失敗しました: ' . $e->getMessage()], 500);
        }
    }

    public function getPosts(Request $request)
    {
        $posts = Post::where('spot_id', $request['id'])->latest()->get();// 投稿を新しい順に取得
        return response()->json($posts);
    }

    public function storePost(Request $request)
    {
        $request->validate([
            'spot_id' => 'required',
            'author' => 'string|max:255',
            'content' => 'required|string|max:1000',
        ]);

        Post::create([
            'spot_id' => $request->spot_id,
            'author' => $request->author,
            'content' => $request->content,
        ]);

        return redirect()->back()->with('message', '投稿されました。');
    }

    public function storePostAPI(Request $request): JsonResponse
    {
        // コントローラ内でバリデーション（FormRequestは使わない）
        $validated = $request->validate([
            'spot_id' => ['required', 'integer', 'exists:spots,id'],
            'author' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:1000'],
        ]);

        // 作成（Resourceは使わず手動でJSON整形）
        $post = Post::create([
            'spot_id' => (int) $validated['spot_id'],
            'author' => $validated['author'] ?? null,
            'content' => $validated['content'],
        ]);

        // 一貫したJSONレスポンス（201 Created）
        return response()->json([
            'message' => 'Created',
            'data' => [
                'id' => $post->id,
                'spot_id' => $post->spot_id,
                'author' => $post->author,
                'content' => $post->content,
                'created_at' => optional($post->created_at)->toIso8601String(),
                'updated_at' => optional($post->updated_at)->toIso8601String(),
            ],
        ], 201);
    }

    public function getNearby(Request $request)
    {
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $radius = 5; // km（必要なら調整）

        $spots = DB::select(
            "SELECT id, category, spot_name, photo_path, evaluation, user_name, created_at,
                (6371 * acos(
                    cos(radians(?)) * cos(radians(ido))
                    * cos(radians(keido) - radians(?))
                    + sin(radians(?)) * sin(radians(ido))
                )) AS distance
            FROM spots
            HAVING distance < ?
            ORDER BY distance ASC
            LIMIT 10", //件数（必要なら調整）
            [$lat, $lng, $lat, $radius]
        );
        return response()->json($spots);
    }
}
