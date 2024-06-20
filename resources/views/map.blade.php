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
  <script src="/js/geolocation.js"></script>
</head>

<body>
  <div id="map" style="width: 100%; height: 500px"></div>
  <script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC2ESS8ztDAAxpYZDfxulply5HeSti6cNA&callback=initMap"></script>
  <button onclick="initMap()">現在地</button>
  {{-- 登録済みの座標をグローバル変数にセット --}}
  <script> var spotData = JSON.parse({!! json_encode($spots) !!});</script>

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

  {{-- スポット登録画面 --}}
  <form action="/form" method="post">
    @csrf
    <div class="container border border-3 rounded">
      <h3 class="text-center">スポット登録</h3>
      {{-- 緯度と経度をhiddenで送信 --}}
      <input type="hidden" id='id_ido' name="ido">
      <input type="hidden" id='id_keido' name="keido">
      <div class="mb-3">
        <label for="name" class="form-label">場所名（呼び名）</label>
        <input type="text" class="form-control" name='spot_name'>
      </div>
      <div class="mb-3">
        <label for="photo" class="form-label">写真</label>
        <input type="file" class="form-control" name="photo_path" rows="3">
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
        <label for="user_name" class="form-label">ニックネーム（登録者）</label>
        <input type="text" class="form-control" name="user_name">
      </div>
      <button type="submit" id='submitButton' class="btn btn-primary" disabled>登録</button>
    </div>
  </form>

  {{-- スポット情報詳細画面 --}}
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