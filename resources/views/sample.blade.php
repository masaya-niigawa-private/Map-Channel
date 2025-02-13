<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0 user-scalable=no">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <title>マップちゃんねる</title>
</head>
<body>
    <img class="tytle-image" src="/icon/マップちゃんねるタイトル.png" alt="マップちゃんねる" />
    <div class="search-area">
        <input type="text" id="input" placeholder="検索" name="search">
        <button onclick="search()">検索</button>
    </div>
    {{-- バリデーションチェックエラー表示 --}}
    @if($errors->any())
    <div eroor_msg>
        <ul>
            @foreach ($errors -> all() as $error)
            <li>{{$error}}</li>
            @endforeach
        </ul>
    </div>
    @endif
    {{-- スポット登録 成功/失敗メッセージ表示 --}}
    @if (session('message'))
    <div class="alert alert-success">
        {{ session('message') }}
    </div>
    @elseif (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif
    <!-- 地図 -->
    <script>
        var spotData = JSON.parse({!! json_encode($spots) !!});
    </script>
    <div class="map" id="map">
        <script src="/js/geolocation.js"></script>
        <script async defer
            src="https://maps.googleapis.com/maps/api/js?key={{$api_key}}&libraries=places&callback=initMap">
        </script>
    </div>
    <!-- 登録リンク案内 -->
    <div class="center-text">
        登録をする場合は下記のリンクから登録ページで行うことができます。
    </div>
    <!-- ジャンル別リンク -->
    <div class="categories">
        <a href="https://smokingarea6.wordpress.com/about/">喫煙所</a>
        <a href="https://smokingarea6.wordpress.com/about/">チルスポ</a>
        <a href="https://smokingarea6.wordpress.com/about/">ビーガン</a>
        <a href="https://smokingarea6.wordpress.com/about/">外国スーパー</a>
        <a href="https://smokingarea6.wordpress.com/about/">抜き（:18歳未満禁止:）</a>
    </div>
    <!-- 問い合わせフォーム -->
    <form action="/opinion" method="post">
        @csrf
        <div class="contact-form">
            <p>MAPの新規ジャンルを増やしてほしいなどの問い合わせはこちらから↓</p>
            <textarea name="opinion" placeholder="ご意見・ご要望を入力してください"></textarea><br>
            <button type="submit">送信</button>
        </div>
    </form>
    {{-- 登録フォーム-ポップアップ画面 --}}
    <dialog class="toroku">
        <button class="close-button" onclick="document.querySelector('.toroku').close()">×</button>
        <h2>位置情報を登録</h2>
        <form class="toroku-form" action="/form" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="id_ido" name="ido">
            <input type="hidden" id="id_keido" name="keido">
            <div class="form-grid">
                <div>
                    <label for="category">カテゴリー</label>
                    <select name="category">
                        <option value="">選択してください</option>
                        <option value="関大">関大</option>
                        <option value="喫煙スポット">喫煙スポット</option>
                        <option value="イベント">イベント</option>
                        <option value="500円以下ランチ">500円以下ランチ</option>
                        <option value="ぴんく">ぴんく</option>
                    </select>
                </div>
                <div>
                    <label for="spot_name">場所名（呼び名）:</label>
                    <input type="text" class="form-control" name="spot_name" placeholder="場所名を入力してください">
                </div>
                <div>
                    <label for="photo">写真:</label>                    
                    <input type="file" class="form-control" name="photo">
                </div>
                <div>
                    <label for="evaluation">評価:</label>
                    <select name="evaluation">
                        <option value="">選択してください</option>
                        <option value="1">⭐</option>
                        <option value="2">⭐⭐</option>
                        <option value="3">⭐⭐⭐</option>
                        <option value="4">⭐⭐⭐⭐</option>
                        <option value="5">⭐⭐⭐⭐⭐</option>
                    </select>
                </div>
                <div>
                    <label for="user_name">登録ユーザー:</label>
                    <input type="text" class="form-control" name="user_name" placeholder="ニックネームを入力してください">
                </div>
                <button type="submit" class="toroku-button">登録</button>
            </div>
        </form>
    </dialog>
    {{-- スポット詳細-ポップアップ画面 --}}
    <dialog class="syosai">
        <button class="close-button" onclick="document.querySelector('.syosai').close()">×</button>
        <!-- 詳細表示のポップアップ -->
        <input type="hidden" id="end_ido">
        <input type="hidden" id="end_keido">
        <div class = "spot-image-container">
            <img class="spot-image" id="spot-image" max-width="40%" height="auto" alt="画像なし" />
        </div>
        <div class="form-group">
            <label for="spot_name" class="form-label">場所：</label>
            <input type="text" class="form-control" id="spot_name" disabled>
        </div>
        <div class="form-group">
            <label for="evaluation" class="form-label">評価：</label>
            <input type="text" class="form-control" id="evaluation" disabled>
        </div>
        <div class="form-group">
            <label for="user_name" class="form-label">登録者：</label>
            <input type="text" class="form-control" id="user_name" disabled>
        </div>
        <div class="form-group">
            <label for="createc_at" class="form-label">登録日時：</label>
            <input type="text" class="form-control" id="createc_at" disabled>
        </div>
    </dialog>
</body>

</html>