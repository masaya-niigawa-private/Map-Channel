let map;
let latitude;
let longitude;
let latlng;
let placesService;
// let directionsService;
// let directionsRenderer;

// 初期表示時に現在地を表示する
async function initMap() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(onGetPositionSuccess, onGetPositionError);
  } else {
    alert("このブラウザは位置情報に対応していません。");
  }
}

// 現在地取得成功時のコールバック
function onGetPositionSuccess(position) {
  //経路オブジェクト
  //directionsService = new google.maps.DirectionsService();
  //directionsRenderer = new google.maps.DirectionsRenderer();
  latitude = position.coords.latitude;
  longitude = position.coords.longitude;
  latlng = new google.maps.LatLng(latitude, longitude);

  map = new google.maps.Map(document.getElementById("map"), {
    zoom: 13,
    center: latlng,
  });
  //レンダラーにマップセット
  //directionsRenderer.setMap(map);

  // Places Serviceを初期化
  placesService = new google.maps.places.PlacesService(map);

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
  //マーカーの配列
  let markers = [];

  //DBに保存されているスポット分を繰り返し
  for (let i = 0; i < spotData.length; i++) {
    const zahyou = { lat: parseFloat(spotData[i].ido), lng: parseFloat(spotData[i].keido) };
    let marker = new google.maps.Marker({
      position: zahyou,
      map: map,
      icon: {
        url: "/icon/grn-pushpin.png",
        scaledSize: new google.maps.Size(40, 40)
      }
    });
    //詳細情報を'syousai'ページに渡す
    marker.addListener('click', function () {
      //経路表示用の'end_ido' 'end_keido'
      // document.getElementById('end_ido').value = parseFloat(spotData[i].ido);
      // document.getElementById('end_keido').value = parseFloat(spotData[i].keido);
      document.getElementById('spot_name').value = (spotData[i].spot_name);
      document.getElementById('evaluation').value = '★'.repeat((spotData[i].evaluation));
      document.getElementById('user_name').value = (spotData[i].user_name);
      document.getElementById('createc_at').value = (spotData[i].created_at).split('T')[0];
      document.getElementById('spot-image').src = "https://mapappp.s3.ap-northeast-3.amazonaws.com/" + (spotData[i].photo_path);
      //navigate('syosai');

      //詳細画面表示（11/14追加）
      const syosai = document.querySelector('.syosai');
      syosai.showModal();

    });
    //配列に入れる
    markers.push(marker);
  }
  // Marker Clustererのオプションを設定
  // const markerCluster = new markerCluster.MarkerClusterer(map, markers,{
  //     imagePath: 'https ://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'

  // });
}

// クリック時のマーカー生成と座標取得
function setupClickListener(map) {
  let marker;
  map.addListener('click', function (event) {
    if (marker) {
      marker.setMap(null);
    }
    marker = new google.maps.Marker({
      position: event.latLng,
      map: map,
      icon: {
        url: "/icon/ylw-pushpin.png",
        scaledSize: new google.maps.Size(40, 40)
      }
    });
    updateInfotable(marker.getPosition().lat(), marker.getPosition().lng());
    //navigate('toroku');

    //登録フォーム表示11/14追加
    const toroku = document.querySelector('.toroku');
    toroku.showModal();
  });
}

// 緯度と経度を<form>のhiddenに渡す
function updateInfotable(lat, lng) {
  document.getElementById('id_ido').value = lat;
  document.getElementById('id_keido').value = lng;
  //buttonController();
}

// windowオブジェクトに入れる
window.initMap = initMap;

//★2024/11/16 登録フォームをポップアップ画面に変更したので不要
//登録ボタンの活性,非活性を制御
// function buttonController() {
//   const id_ido = document.getElementById('id_ido');
//   const submitButton = document.getElementById('submitButton');
//   //地図をクリックされていれば登録ボタンを活性にする
//   id_ido.addEventListener('input', function () {
//     if (id_ido.value.trim() !== '') {
//       submitButton.disabled = false;
//     } else {
//       submitButton.disabled = true;
//     }
//   });
//   if (id_ido.value.trim() !== '') {
//     submitButton.disabled = false;
//   } else {
//     submitButton.disabled = true;
//   }
// }

//場所検索ボックスのセットアップ処理
function search() {
  const query = document.getElementById("input").value;
  if (!query) {
    alert("Please enter a place to search");
    return;
  }
  searchQuery(query);
}

// 場所を検索
function searchQuery(query) {
  const request = {
    query: query,
    fields: ['name', 'geometry'],
  };

  placesService.findPlaceFromQuery(request, function (results, status) {
    if (status === google.maps.places.PlacesServiceStatus.OK) {
      for (let i = 0; i < results.length; i++) {
        // 検索結果をマップに表示
        const place = results[i];
        new google.maps.Marker({
          position: place.geometry.location,
          map: map,
          title: place.name
        });

        // マップの中心を検索結果に移動
        map.setCenter(place.geometry.location);
      }
    } else {
      console.error('Place not found:', status);
    }
  });
}

//経路をマップに表示
// function calcRoute() {
//   //コンストラクタの使い方→new google.maps.LatLng(経度,緯度)
//   const start = new google.maps.LatLng(latitude, longitude);
//   const end_ido = document.getElementById('end_ido').value;
//   const end_keido = document.getElementById('end_keido').value;
//   const end = new google.maps.LatLng(end_ido, end_keido);

//   const request = {
//     origin: start,
//     destination: end,
//     travelMode: 'DRIVING'
//   };
//   directionsService.route(request, function (result, status) {
//     if (status == 'OK') {
//       directionsRenderer.setDirections(result);
//     }
//   });
// }

//SPAするため
// function navigate(pageId) {
//   // すべてのページを非表示にする
//   const pages = document.querySelectorAll('.page');
//   pages.forEach(page => page.classList.remove('active'));

//   // 指定されたページのみを表示する
//   const activePage = document.getElementById(pageId);
//   if (activePage) {
//       activePage.classList.add('active');
//   }
// }

//2024/11/16
//ダイアログ外をクリックした場合に閉じる
document.addEventListener('click', (event) => {
  if (event.target.closest('.map') || event.target.closest('.marker')) {
    return;
  }
  const dialogs = document.querySelectorAll('dialog');
  dialogs.forEach((dialog) => {
    if (dialog.open && event.target === dialog) {
      dialog.close();
    }
  });
});