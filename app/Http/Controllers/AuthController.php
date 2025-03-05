<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    //ログインフォームを表示
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        // バリデーション
        $request->validate([
            'name' => 'required',
            // 'email' => 'email',
            'password' => 'required'
        ]);

        // レコードを取得
        $user = User::where('name', $request->name)->first();

        // 認証チェック
        if ($user && Hash::check($request->password, $user->password)) {
            // セッションに保存
            Session::put('user_id', $user->id);
            Session::put('user_name', $user->name);

            return response()->json(['success' => true, 'user_name' => $user->name]); // 成功レスポンス
        }

        return response()->json(['success' => false], 401); // 失敗レスポンス
    }

    //ログアウト
    public function logout()
    {
        Session::flush();
        return redirect()->route('login.form');
    }

    // 登録フォームを表示
    public function showRegisterForm()
    {
        return view('register');
    }

    // ユーザー登録処理
    public function register(Request $request)
    {
        // バリデーション
        $request->validate([
            'name' => 'required|string|max:255|unique:users',
            'email' => 'nullable|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // ユーザー作成
        $userData = [
            'name' => $request->name,
            'password' => Hash::make($request->password), // パスワードをハッシュ化
        ];
        
        // メールアドレスが入力されている場合のみ追加
        if (!empty($request->email)) {
            $userData['email'] = $request->email;
        }
        
        // ユーザー作成
        $user = User::create($userData);        

        // セッションに保存（ログイン状態にする）
        Session::put('user_id', $user->id);
        Session::put('user_name', $user->name);

        // 登録後のリダイレクト
        return redirect()->route('dashboard');
    }
}
