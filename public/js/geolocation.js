
// 初期表示時に現在地を表示する
function initMap() {

  var Marker;

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      function (position) {
        //現在地座標を取得
        const latitube = position.coords.latitude;
        const longitude = position.coords.longitude;
        const latlng = new google.maps.LatLng(latitube, longitude);

        // マップ生成
        const map = new google.maps.Map(document.getElementById("map"), {
          zoom: 12,
          center: latlng,
        });

        //初期表示時のマーカーは不要かもね、、（クリックイベントした時で良い）
        //マーカーの設置
        // new google.maps.Marker({
        //   position: latlng,
        //   map: map,
        // });

        //クリックイベント
        //①クリックした地点にマーカー立てる
        //②マーカーの緯度経度を取得する
        //③取得した緯度経度をhtmlに表示する

        //試作No.3（2024/06/08）
        map.addListener('click', function (event) {
          if (Marker) { Marker.setMap(null) };
          Marker = new google.maps.Marker({
            position: event.latLng,
            draggable: true,
            map: map
          });
          infotable(Marker.getPosition().lat(),
            Marker.getPosition().lng());
        });

      },
      function (error) {
        alert("エラーです！");
      }
    );
    // ブラウザがgeolocation_APIに対応していない場合
  } else {
    alert("このブウラウザは位置情報に対応していません。");
  }
}

//★★改修ポイント★★
// マップ初期表示＆ボタン押下で処理が被ってるので、⇒１つのメソッドにまとめる

// 現在位置を反映ボタン
const getNow = () => {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      function (position) {
        const map = new google.maps.Map(document.getElementById("map"));
        const latitube = position.coords.latitude;
        const longitude = position.coords.longitude;
        const latlng = new google.maps.LatLng(latitube, longitude);

        const opts = {
          zoom: 13,
          center: latlng,
        };

        new google.maps.Marker({
          position: latlng,
          map: map,
        });

        // setOptionでオプションを上書き反映
        map.setOptions(opts);
      },
      function (error) {
        alert("エラーです！");
      }
    );
  } else {
    alert("このブウラウザは位置情報に対応していません。");
  }
};


/* 緯度経度を表示する */
// function getClickLatLng(latlng) {
//   alert('緯度: ' + latlng.lat + ' 経度: ' + latlng.lng);
// }

/* 緯度経度を表示する */
function infotable(ido, keido ) {
  document.getElementById('id_ido').value = ido;
  document.getElementById('id_keido').value = keido;
}