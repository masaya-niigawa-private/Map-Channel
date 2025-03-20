<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Spot;
use App\Models\Comment;
use App\Models\Opinion;
use App\Models\Photo;

class AdminController extends Controller
{
    //スポット登録
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
            return redirect('/')->with('message', '正常に登録されました。');
        } catch (\Exception $e) {
            // 例外発生時にエラーメッセージを表示
            return back()->with('error', '登録に失敗しました。' . $e->getMessage());
        }
    }

    //spotテーブルから全データ取得
    public function get()
    {
        $all_spots = Spot::all();
        $all_spots_json = json_encode($all_spots);
        return $all_spots_json;
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
            return redirect('/')->with('message', '送信されました。');
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

    //修正機能
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
        return response()->json(['message' => '更新が完了しました']);
    }
}
