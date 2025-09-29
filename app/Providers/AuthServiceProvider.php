<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // "firebase" という request-based ドライバを登録
        Auth::viaRequest('firebase', function ($request) {
            // 1) Bearer トークンを取り出す
            $token = $request->bearerToken();
            if (!$token) {
                return null; // 未認証扱い（後段のAuthenticateが401にする）
            }

            try {
                /** @var FirebaseAuth $firebase */
                $firebase = app(FirebaseAuth::class);

                // 2) IDトークン検証（署名・有効期限・aud/iss など含む）
                $verifiedIdToken = $firebase->verifyIdToken($token);

                // 3) Firebase UID・メール等を取得
                $uid = $verifiedIdToken->claims()->get('sub'); // = uid
                $email = $verifiedIdToken->claims()->get('email');

                // 4) アプリ側ユーザーを解決（なければ作成／あるいは404扱い等は要件次第）
                $user = User::where('firebase_uid', $uid)->first();
                if (!$user) {
                    // 最小実装：自動作成（要件に合わせて制御）
                    $user = User::create([
                        'name' => $email ?? ('user_' . $uid),
                        'email' => $email ?? (sprintf('%s@firebase.local', $uid)),
                        'firebase_uid' => $uid,
                        // 必要に応じて他の初期値
                    ]);
                }

                return $user; // ★返したUserが以降の Auth::user() になります
            } catch (\Throwable $e) {
                // ログして未認証に落とす
                logger()->warning('[auth:firebase] token verify failed', ['err' => $e->getMessage()]);
                return null;
            }
        });
    }
}
