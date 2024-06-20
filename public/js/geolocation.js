
// 初期表示時に現在地を表示する
function initMap() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(onGetPositionSuccess, onGetPositionError);
  } else {
    alert("このブラウザは位置情報に対応していません。");
  }
}

// 現在地取得成功時のコールバック
function onGetPositionSuccess(position) {
  const latitude = position.coords.latitude;
  const longitude = position.coords.longitude;
  const latlng = new google.maps.LatLng(latitude, longitude);

  const map = new google.maps.Map(document.getElementById("map"), {
    zoom: 12,
    center: latlng,
  });

  // 既存スポットのマーカーを生成
  addExistingMarkers(map);

  // クリック地点のマーカーを設定
  setupClickListener(map);
}

// 現在地取得失敗時のコールバック
function onGetPositionError() {
  alert("位置情報の取得に失敗しました。");
}

// 既存スポットのマーカーを追加
function addExistingMarkers(map) {
  for (let i = 0; i < spotData.length; i++) {
    const zahyou = { lat: parseFloat(spotData[i].ido), lng: parseFloat(spotData[i].keido) };
    new google.maps.Marker({
      position: zahyou,
      map: map,
      icon: {
        url: "/icon/cannabis.png",
        scaledSize: new google.maps.Size(60,60)
      }
    });
  }
}

// マップクリック時のマーカー生成と座標表示
function setupClickListener(map) {
  let marker;
  map.addListener('click', function (event) {
    if (marker) {
      marker.setMap(null);
    }
    marker = new google.maps.Marker({
      position: event.latLng,
      map: map,
    });
    updateInfotable(marker.getPosition().lat(), marker.getPosition().lng());
  });
}

// 緯度と経度を<form>のhiddenに渡す
function updateInfotable(lat, lng) {
  document.getElementById('id_ido').value = lat;
  document.getElementById('id_keido').value = lng;
  buttonController();
}

// windowオブジェクトに入れる
window.initMap = initMap;

//登録ボタンの活性,非活性を制御
function buttonController(){
  const id_ido = document.getElementById('id_ido');
  const submitButton = document.getElementById('submitButton');
  //地図をクリック(=緯度と経度が選択)されたら、登録ボタンを活性にする
  id_ido.addEventListener('input',function(){
    if(id_ido.value.trim() !==''){
      submitButton.disabled = false;
    }else{
      submitButton.disabled = true;
    }
  });
  if (id_ido.value.trim() !== '') {
    submitButton.disabled = false;
  } else {
    submitButton.disabled = true;
  }
}