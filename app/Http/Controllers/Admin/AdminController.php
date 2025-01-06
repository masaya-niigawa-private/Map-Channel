<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Spot;
use App\Models\Opinion;

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
            $file = $request->file('photo');
            if ($file) {
                $path = $file->store('photo', 's3');
                $spot->photo_path = $path;
            };
            //DBに保存
            $spot->save();
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
}
