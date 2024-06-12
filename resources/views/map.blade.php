<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>喫煙所まっぷ</title>

  <script src="/js/geolocation.js" defer></script>
  <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC2ESS8ztDAAxpYZDfxulply5HeSti6cNA&callback=initMap"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
  <div id="map" style="width: 100%; height: 500px"></div>

  {{-- 座標取得＿確認用
  <table style="width:100%;border:0">
    <tr style="background-color:#dddddd">
      <th style="width:20%">項目</th>
      <th>情報</th>
    </tr>
    <tr>
      <td>緯度</td>
      <td id="id_ido"></td>
    </tr>
    <tr>
      <td>経度</td>
      <td id="id_keido"></td>
    </tr>
  </table> --}}

  <button onclick="getNow()">現在地反映</button>

  {{-- 下記の２画面の切り替え制御する
  ・登録フォーム
  ・詳細情報 --}}

  {{-- 登録画面 --}}
  <form action="/form" method="post">
    @csrf
    <div class="container border border-3 rounded">
      <h2 class="text-center">ちるスポット登録</h2>
      
      {{-- 緯度と経度をhiddenで送信 --}}
      <input type="input" id="id_ido">
      <input type="input" id="id_keido">
      <div class="mb-3">
        <label for="name" class="form-label">場所名称</label>
        <input type="text" class="form-control" id="name">
      </div>
      <div class="mb-3">
        <label for="photo" class="form-label">写真</label>
        <input type="file" class="form-control" id="photo" rows="3">
      </div>
      <div class="mb-3">
        <label for="evaluation" class="form-label">評価　</label>
        <select name="evaluation">
          <option value="">選択してください</option>
          <option value="1">1</option>
          <option value="2">2</option>
          <option value="3">3</option>
          <option value="4">4</option>
          <option value="5">5</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="username" class="form-label">ニックネーム（登録者）</label>
        <input type="text" class="form-control" id="username">
      </div>
      <button type="submit" class="btn btn-primary">登録</button>
    </div>
  </form>

  {{-- 詳細画面 --}}
  {{-- <div class="container text-center">
    <div class="row">
      <div class="col-5 border">
        <img src="https://news.walkerplus.com/article/1121391/11455268_615.jpg" class="img-thumbnail" alt="画像なし">
        <br>
        <br>到着時間
        <br>
        <img width="100" height="100" src="https://cdn.pixabay.com/photo/2018/08/10/20/38/walking-3597539_1280.jpg"
          class="img-thumbnail">
      </div>
      <div class="col border">
        場所名称
      </div>
    </div>
  </div> --}}



</body>

</html>