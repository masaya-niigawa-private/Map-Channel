<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>喫煙所まっぷ</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  {{-- マーカークラスタリングAPI
  <script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
  <script src="https://unpkg.com/@googlemaps/markerclusterer@2.5.3/dist/index.min.js"></script> --}}
  <script src="/js/geolocation.js"></script>
</head>
<body>
  <div id="map" style="width: 100%; height: 430px"></div>
  <script async defer
    src="https://maps.googleapis.com/maps/api/js?key={{$api_key}}&libraries=places&callback=initMap">
  </script>
  <button onclick="initMap()">現在位置</button>
  {{-- 検索Box --}}
  <div class="search-container">
    <input type="text" id="input" class="search-box" placeholder="Search for places...">
    <button class="search-button" id="searchButton" onclick="search()">検索</button>
  </div>
  {{-- 登録済みの座標をグローバル変数にセット --}}
  <script>
    var spotData = JSON.parse({!! json_encode($spots) !!});
  </script>
  <div id="toroku" class="page active">
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
    {{-- スポット登録フォーム --}}
    <form action="/form" method="post" enctype='multipart/form-data'>
      @csrf
      <div class="container form border border-3 rounded">
        <h3 class="text-center">スポット登録</h3>
        <input type="hidden" id='id_ido' name="ido">
        <input type="hidden" id='id_keido' name="keido">
        <div class="mb-3 row">
          <label for="spot_name" class="col-sm-4 col-form-label">場所名（呼び名）</label>
          <div class="col-sm-8">
            <input type="text" class="form-control" name="spot_name" placeholder="場所名を入力してください">
          </div>
        </div>
        <div class="mb-3 row">
          <label for="photo" class="col-sm-4 col-form-label">写真</label>
          <div class="col-sm-8">
            <input type="file" class="form-control" name="photo">
          </div>
        </div>
        <div class="mb-3 row">
          <label for="evaluation" class="col-sm-4 col-form-label">評価</label>
          <div class="col-sm-8">
            <select class="form-select" name="evaluation">
              <option value="">選択してください</option>
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
              <option value="5">5</option>
            </select>
          </div>
        </div>
        <div class="mb-3 row">
          <label for="user_name" class="col-sm-4 col-form-label">登録ユーザー</label>
          <div class="col-sm-8">
            <input type="text" class="form-control" name="user_name" placeholder="ニックネームを入力してください">
          </div>
        </div>
        <button type="submit" id='submitButton' class="btn btn-primary w-100" disabled>登録</button>
      </div>
    </form>
  </div>
  {{-- スポット詳細画面 --}}
  <div id="syosai" class="page">
    <div class="container syousai mt-4">
      <div class="row">
        <div class="col-md-5 border">
            <img id="spot-image" width="500" alt="Spot Image" />
        </div>
        <div class="col-md-7 border">
          <img width="70" height="70" src="https://cdn.pixabay.com/photo/2018/08/10/20/38/walking-3597539_1280.jpg"
            class="img-thumbnail">　　到着予定時間：◯◯分
          <button id="keiroButton" onclick="calcRoute()">経路を表示して</button>
          {{-- 経路表示用の目的地 --}}
          <input type="hidden" id="end_ido">
          <input type="hidden" id="end_keido">
          <div class="container">
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
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>