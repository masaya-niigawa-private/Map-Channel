let map;
let latlng;
let placesService;
let directionsService;


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
  directionsService = new google.maps.DirectionsService();
  var directionsRenderer = new google.maps.DirectionsRenderer();

  const latitude = position.coords.latitude;
  const longitude = position.coords.longitude;
  latlng = new google.maps.LatLng(latitude, longitude);

  map = new google.maps.Map(document.getElementById("map"), {
    zoom: 13,
    center: latlng,
  });
  //レンダラーにマップセット
  directionsRenderer.setMap(map);

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
  var markers = [];

  for (let i = 0; i < spotData.length; i++) {
    const zahyou = { lat: parseFloat(spotData[i].ido), lng: parseFloat(spotData[i].keido) };
    var marker = new google.maps.Marker({
      position: zahyou,
      map: map,
      icon: {
        url: "/icon/grn-pushpin.png",
        scaledSize: new google.maps.Size(40,40)
      }
    });
    //詳細情報を'syousai'ページに渡す
    marker.addListener('click', function() {
      document.getElementById('start').value = latlng;
      document.getElementById('end').value = new google.maps.LatLng(zahyou);
      document.getElementById('spot_name').value = (spotData[i].spot_name);
      document.getElementById('evaluation').value = (spotData[i].evaluation);
      document.getElementById('user_name').value = (spotData[i].user_name);
      document.getElementById('createc_at').value = (spotData[i].created_at).split('T')[0];
      navigate('syosai');
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
        scaledSize: new google.maps.Size(40,40)
      }
    });
    updateInfotable(marker.getPosition().lat(), marker.getPosition().lng());
    navigate('toroku');
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
  //地図をクリックされていれば登録ボタンを活性にする
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

//場所検索ボックスのセットアップ処理（開発中）
function search() {

  const query = document.getElementById("input").value;
  if (!query) {
      alert("Please enter a place to search");
      return;
  }
  searchQuery(query);
}

// 指定されたクエリから場所を検索
function searchQuery(query) {
  const request = {
      query: query,
      fields: ['name', 'geometry'],
  };

  placesService.findPlaceFromQuery(request, function(results, status) {
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

//経路計算
function calcRoute() {
  var start = document.getElementById('start').value;
  var end = document.getElementById('end').value;
  console.log(start);
  console.log(end);
  
  var request = {
    origin: start,
    destination: end,
    travelMode: 'DRIVING'
  };
  directionsService.route(request, function(result, status) {
    if (status == 'OK') {
      directionsRenderer.setDirections(result);
    }
  });
}

//ページナビゲーション
function navigate(pageId) {
  // すべてのページを非表示にする
  const pages = document.querySelectorAll('.page');
  pages.forEach(page => page.classList.remove('active'));

  // 指定されたページのみを表示する
  const activePage = document.getElementById(pageId);
  if (activePage) {
      activePage.classList.add('active');
  }
}