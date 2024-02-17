// 初回で現在地を取得して反映
function initMap() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      function (position) {
        const latitube = position.coords.latitude;
        const longitude = position.coords.longitude;

        // LatLngは中心を指定するクラス
        const latlng = new google.maps.LatLng(latitube, longitude); //中心の緯度, 経度

        // new google.maps.Map で新規マップ作成
        // オプションでズームとか真ん中とか設定できる
        const map = new google.maps.Map(document.getElementById("map"), {
          zoom: 14, //ズームの調整
          center: latlng, // 中心の設定
        });

        // 地図上の赤いマーカーの場所
        new google.maps.Marker({
          position: latlng,
          map: map,
        });
      },
      function (error) {
        alert("エラーです！");
      }
    );
  } else {
    alert("このブウラウザは位置情報に対応していません。");
  }
}

// ボタンを押すと現在地を反映
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