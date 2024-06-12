<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coordinate;

class AdminController extends Controller
{
    public function store(Request $request)
    {
        try {
            $coordinate = new Coordinate();
            
            // 投稿フォームから送信されたデータを取得し、インスタンスの属性に代入する
            $coordinate -> name = $request->input('coodinate');
            $coordinate->save();
            return redirect('/')->with('message', '登録が完了しました！');
            
        } catch (\Exception $e) {
            return back()->with('message', '登録に失敗しました。' . $e->getMessage());
        }
    }
}
