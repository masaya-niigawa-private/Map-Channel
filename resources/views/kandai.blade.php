<!DOCTYPE html>
<html lang="ja">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0 user-scalable=no">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <title>マップちゃんねる</title>
</head>

<body>
    <div class="container">
        <img class="tytle-image" src="/icon/マップちゃんねる_ver2.PNG" alt="マップちゃんねる" />
        <!-- <div class="search-area">
            <input type="text" id="input" placeholder="検索" name="search">
            <button onclick="search()">検索</button>
        </div> -->
    </div>
    <div id="authContainer">
        <button class="loginBtn" type="button" onclick="openPopup()">ログイン</button>
    </div>
    {{-- バリデーションチェックエラー表示 --}}
    @if($errors->any())
        <div eroor_msg>
            <ul>
                @foreach ($errors->all() as $error)
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
            src="https://maps.googleapis.com/maps/api/js?key={{$api_key}}&libraries=places&callback=initMap_kandai">
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

    <!-- 登録フォーム画面 -->
    <dialog class="toroku">
        <button class="close-button" onclick="document.querySelector('.toroku').close()">×</button>
        <div class="toroku-tytle">まっぷ登録</div>
        <form class="toroku-form" action="/form" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="id_ido" name="ido">
            <input type="hidden" id="id_keido" name="keido">

            <!-- ページ 1 -->
            <div id="page1">
                <div>
                    <label for="category">カテゴリー</label>
                    <select name="category">
                        <option value="">選択してください</option>
                        <option value="関大">関大</option>
                        <option value="喫煙スポット">喫煙スポット</option>
                        <option value="イベント">イベント</option>
                        <option value="500円以下ランチ">500円以下ランチ</option>
                        <option value="ぴんく">ぴんく</option>
                        <option value="キッチンカー">キッチンカー</option>
                    </select>
                </div>
                <div>
                    <label for="spot_name">名前</label>
                    <input type="text" class="toroku-form-control" name="spot_name" placeholder="場所名を入力してください">
                </div>
                <div>
                    <label for="evaluation">評価</label>
                    <select name="evaluation">
                        <option value="">選択してください</option>
                        <option value="1">⭐</option>
                        <option value="2">⭐⭐</option>
                        <option value="3">⭐⭐⭐</option>
                        <option value="4">⭐⭐⭐⭐</option>
                        <option value="5">⭐⭐⭐⭐⭐</option>
                    </select>
                </div>
                <button class="pageButton" type="button" onclick="nextPage()">次ページ</button>
            </div>

            <!-- ページ 2 -->
            <div id="page2" style="display: none;">
                <div>
                    <label for="photo">画像</label>
                    <input type="file" class="toroku-form-control" name="photo[]" multiple>
                </div>
                <div class="user-container">
                    <label for="login_user_name" style="margin-top:initial">登録者</label>
                    <input readonly id="login_user_name" class="toroku-form-control" type="text" style="display:none">
                    <button class="loginButton" type="button" onclick="openPopup()">ログイン</button>
                </div>
                <div>
                    <label for="comment">コメント</label>
                    <textarea class="textarea" name="comment" rows="3" cols="30" style="resize: none;"
                        placeholder="コメントを入力してください"></textarea>
                </div>
                <button class="pageButton" type="button" onclick="prevPage()">前ページ</button>
                <button type="submit" class="toroku-button">登録</button>
            </div>
        </form>
    </dialog>

    <!-- ログインポップアップウィンドウ -->
    <dialog class="loginPopup">
        <div class="popup-content" style="color:black">
            <span class="close-btn" onclick="closePopup()">×</span>
            <h2>ログイン</h2>
            <form id="loginForm" action="{{ route('login') }}" method="POST">
                @csrf
                <label for="name">ユーザー名:</label>
                <input name="name" required>
                <br>
                <label for="password">パスワード:</label>
                <input type="password" name="password" required>
                <br>
                <button type="submit">ログイン</button>
            </form>
        </div>
    </dialog>

    <!-- スポット詳細ポップアップ -->
    <dialog class="syosai">
        <button class="close-button" onclick="document.querySelector('.syosai').close()">×</button>
        <div class="syosai-popup-content">
            <div class="spot-image-popup-container">
                <img class="spot-image-popup" id="spot-image1" max-width="40%" height="auto" alt="画像なし" />
            </div>
            <div class="syosai-popup-group">
                <label for="spot_name" class="syosai-popup-label">場所：</label>
                <input type="text" class="syosai-popup-control" id="spot_name1" disabled>
            </div>
        </div>
    </dialog>

    <!-- スポット詳細（画面下部） -->
    <!-- <div class="detail-container">
        <div class="spot-image-container" id="spot-image2">
            </div>
            <div class="info-container">
            <button id="editButton" onclick="editButtonClick()">修正</button>
            <button id="editSubmitButton" onclick="editSubmitButtonClick()">修正確定</button>
            <input type="hidden" id='spot_id'>
            <div class="form-group">
                <label for="spot_name" class="syosai-form-label">場所</label>
                <input type="text" class="syosai-form-control" id="spot_name2" disabled>
            </div>
            <div class="form-group">
                <label for="evaluation" class="syosai-form-label">評価</label>
                <div id="evaluationContainer">
                    <input type="text" class="syosai-form-control" id="evaluationDisplay" disabled>
                </div>
            </div>
            <div class="form-group">
                <label for="user_name" class="syosai-form-label">登録者</label>
                <input type="text" class="syosai-form-control" id="user_name" disabled>
            </div>
            <div class="form-group">
                <label for="created_at" class="syosai-form-label">登録日</label>
                <input type="text" class="syosai-form-control" id="created_at" disabled>
            </div>
            <div class="form-group">
                <label for="comment" class="syosai-form-label">コメント</label>
                <input type="text" class="syosai-form-control" id="comment" disabled>
            </div>
        </div>
    </div> -->
    <main class="detail-container">
        <button id="editButton" onclick="editButtonClick()">修正</button>
        <button id="editSubmitButton" onclick="editSubmitButtonClick()">修正確定</button>
        <div class="category">
            <label>カテゴリー</label>
            <input id="category" type="text" disabled>
        </div>
        <div class="spot_name">
            <label class="">場所</label>
            <input id="spot_name2" type="text" disabled>
        </div>
        <div class="main-image">
            <img id="main-image" alt="画像なし">
        </div>
        <img src="thumb1.png" alt="サブ画像１" class="sub-image1">
        <img src="thumb2.png" alt="サブ画像２" class="sub-image2">
        <img src="thumb3.png" alt="サブ画像３" class="sub-image3">
        <button class="add-btn">＋</button>
        <div class="info-left">
            <label>営業時間</label>
            <input type="text" value="午前9時〜午後9時" disabled>
            <label>登録者コメント</label>
            <input type="text" id="comment" disabled>
            <div id="evaluationContainer">
                <label>評価</label>
                <input type="text" id="evaluationDisplay" disabled>
            </div>
            <label>登録者</label>
            <input type="text" id="user_name" disabled>
            <label>登録日</label>
            <input type="text" id="created_at" disabled>
        </div>
        <div class="info-right">
            <label>スレッド</label>
            <form action="{{ route('store.post')}}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="spot_id" id="spot_id">
                <input type="text" name="author" placeholder="投稿者名">
                <textarea name="content" placeholder="投稿内容" required></textarea>
                <button type="submit">投稿</button>
            </form>
            <div id="postContainer"></div>
        </div>
    </main>
    </main>
</body>

</html>