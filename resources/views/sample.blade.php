<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MYMAP</title>
    <style>
        /* 背景を黒に設定 */
        body {
            font-family: Arial, sans-serif;
            background-color: #000; /* 背景色を黒に設定 */
            color: #fff; /* テキストカラーを白に設定 */
        }
        .title {
            font-size: 2em;
            color: #00FF00;
            text-align: center;
            margin-top: 20px;
        }
        /* 横スクロールアニメーション */
        .welcome-message {
            color: #00FF00;
            font-size: 1.2em;
            overflow: hidden;
            white-space: nowrap;
            animation: scroll 20s linear infinite;
        }
        @keyframes scroll {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        .search-box {
            text-align: center;
            margin: 20px;
        }
        .map {
            width: 100%;
            height: 500px;
            background-color: #333; /* 地図背景の仮設定 */
            text-align: center;
            margin: 20px 0;
            color: #bbb;
        }
        .map span {
            display: inline-block;
            margin-top: 220px;
            font-size: 1.5em;
        }
        .rating {
            text-align: left;
            font-size: 1.5em;
            margin-left: 20px;
        }
        .center-text {
            text-align: center;
            margin: 10px;
        }
        .categories {
            text-align: center;
            margin-top: 10px;
        }
        .categories a {
            font-size: 1.1em;
            color: #00FF00;
            text-decoration: none;
            margin: 0 10px;
        }
        .contact-form {
            text-align: center;
            margin-top: 20px;
            color: #00FF00; /* 問い合わせメッセージの色を緑に設定 */
        }
        .contact-form textarea {
            width: 80%;
            height: 100px;
            background-color: #222; /* テキストエリアの背景を濃い灰色に */
            color: #fff;
            border: 1px solid #555;
        }
        .contact-form button {
            padding: 10px 20px;
            font-size: 1em;
            background-color: #00FF00;
            border: none;
            color: #000;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- タイトル -->
    <div class="title">MYMAP</div>
    <!-- 説明文（横スクロール） -->
    <div class="welcome-message">
        ようこそ、MYMAPへ ここでは自分の共有したい位置情報を登録できます。また、知りたい位置情報を知ることもできます。
    </div>
    <!-- 検索フォーム -->
    <div class="search-box">
        <input type="text" placeholder="検索" name="search">
        <button>検索</button>
    </div>
    <!-- 地図表示の代替 (仮の場所) -->
    <script>
      var spotData = JSON.parse({!! json_encode($spots) !!});
    </script>
    <div class="map" id="map">
      <script src="/js/geolocation.js"></script>
      <script async defer
      src="https://maps.googleapis.com/maps/api/js?key={{$api_key}}&libraries=places&callback=initMap">
    </script>
    </div>
    <!-- 評価表示 -->
    <div class="rating">★</div>
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
    <div class="contact-form">
        <p>MAPの新規ジャンルを増やしてほしいなどの問い合わせはこちらから↓</p>
        <textarea placeholder="ご意見・ご要望を入力してください"></textarea><br>
        <button>送信</button>
    </div>
</body>
</html>