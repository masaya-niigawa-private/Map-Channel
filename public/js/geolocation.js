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
    gestureHandling: "greedy",//指1本操作
    mapTypeControl: false,//「地図」「航空写真」を非表示
    fullscreenControl: false,//「フルスクリーン」ボタン無効化
    streetViewControl: false,
    zoomControl: false,
    styles: [
      {
        featureType: "poi.business",//商業施設を非表示
        elementType: "labels",
        stylers: [{ visibility: "off" }]
      }
    ]
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
async function addExistingMarkers(map) {
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
    //マーカークリック時に詳細表示
    marker.addListener('click', async function () {
      //修正状態の場合はリセット
      if (window.getComputedStyle(editButton).display === "none") {
        resetEditState();
      }
      document.getElementById('spot_id').value = parseFloat(spotData[i].id);
      document.getElementById('spot_name1').value = (spotData[i].spot_name);
      document.getElementById('spot_name2').value = (spotData[i].spot_name);
      document.getElementById('evaluationDisplay').value = '★'.repeat((spotData[i].evaluation));
      document.getElementById('user_name').value = (spotData[i].user_name);
      const createdAtJST = new Date(spotData[i].created_at);
      document.getElementById('created_at').value = createdAtJST.toLocaleDateString('ja-JP');

      const id = spotData[i].id;
      //コメントを検索
      try {
        const response = await fetch(`/comments/${id}`);
        const comments = await response.json();
        const commentSection = document.getElementById('comment');
        if (comments.length > 0) {
          commentSection.value = comments[0].comment; // inputのvalueに設定
        } else {
          commentSection.value = ''; // コメントがなければ空にする
        }
      } catch (error) {
        alert(error.message);
      }

      //写真を検索
      try {
        const response = await fetch(`/photos/${id}`);
        const photos = await response.json();
        if (photos.length > 0) {
          image1 = document.getElementById('spot-image1');
          image2 = document.getElementById('spot-image2');
          image1.src = "https://mapappp.s3.ap-northeast-3.amazonaws.com/" + photos[0].photo_path;
          image2.innerHTML = '';
          photos.forEach(photo => {
            const img = document.createElement('img');
            img.src = "https://mapappp.s3.ap-northeast-3.amazonaws.com/" + photo.photo_path;
            img.alt = "画像なし"
            image2.appendChild(img);
          });
        }
      } catch (error) {
        alert(error.message);
      }

      //詳細ポップアップ表示（11/14追加）
      const syosai = document.querySelector('.syosai');
      syosai.showModal();

      //修正ボタン表示
      document.getElementById("editButton").style.display = "block";

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

//登録フォームバリエーションチェック（2025/01/11）
document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.toroku-form');
  const button = document.querySelector('.toroku-button');
  button.addEventListener('click', (event) => {
    event.preventDefault(); // デフォルトのフォーム送信を防止
    // バリデーション実行
    if (validateForm()) {
      form.submit(); // チェックを通過した場合のみフォーム送信
    }
  });

  function validateForm() {
    const errormsg = [];
    const inputSpot_name = document.querySelector('input[name="spot_name"]');
    const inputEvaluation = document.querySelector('select[name="evaluation"]');
    if (inputEvaluation.value === '') {
      errormsg.push('評価を選択してください')
    }
    if (inputSpot_name.value === '') {
      errormsg.push('場所名（呼び名）を入力してください')
    }
    // エラーがあればアラートを表示し、falseを返す
    if (errormsg.length > 0) {
      alert(errormsg.join('\n')); // エラーメッセージを改行で区切って表示
      return false;
    }
    return true; // 全てのチェックを通過
  }
});

//登録フォームページ移動
function nextPage() {
  document.getElementById('page1').style.display = 'none';
  document.getElementById('page2').style.display = 'block';
}

function prevPage() {
  document.getElementById('page2').style.display = 'none';
  document.getElementById('page1').style.display = 'block';
}

// ポップアップを開く
function openPopup() {
  document.querySelector(".loginPopup").showModal();
}

// ポップアップを閉じる
function closePopup() {
  document.querySelector(".loginPopup").close();
}

//ログイン認証
document.addEventListener('DOMContentLoaded', function () {
  document.getElementById('loginForm').addEventListener('submit', function (event) {
    event.preventDefault(); // デフォルトのフォーム送信を防ぐ

    let formData = new FormData(this);
    const dialog = document.querySelector(".toroku");

    fetch("/login", {
      method: "POST",
      body: formData,
      headers: {
        "X-CSRF-TOKEN": document.querySelector('input[name=_token]').value
      }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          closePopup();
          alert("ログイン成功！");
          if (!dialog.open) {
            location.reload();
          }
          document.getElementById("login_user_name").value = data.user_name;
          document.getElementById("login_user_name").style.display = 'block';
          document.querySelector(".loginButton").style.display = "none";
        } else {
          alert("ログインに失敗しました");
        }
      })
      .catch(error => console.error('Error:', error));
  });
});

document.addEventListener("DOMContentLoaded", function () {
  fetch("/check-login") // ログイン状態を確認
    .then(response => response.json())
    .then(data => {
      if (data.logged_in) {
        document.getElementById("login_user_name").value = data.user_name;
        document.getElementById("login_user_name").style.display = 'block';
        document.querySelector(".loginButton").style.display = "none";
        document.getElementById("authContainer").innerHTML =
          `<span class="loggedInText">ログイン中: ${data.user_name}</span>`;
      }
    })
  //.catch(error => console.error("Error:", error));
});

// 修正ボタンの処理
function editButtonClick() {
  document.getElementById("editButton").style.display = "none";
  document.getElementById("editSubmitButton").style.display = "block";
  document.getElementById("spot_name2").disabled = false;
  const evaluationDisplay = document.getElementById("evaluationDisplay");
  evaluationDisplay.style.display = "none";
  document.getElementById("user_name").disabled = false;
  document.getElementById("created_at").disabled = false;
  document.getElementById("comment").disabled = false;
  //以下 評価セレクトボックス表示
  const selectElement = document.createElement('select');
  const value = evaluationDisplay.value.length;
  selectElement.id = 'evaluationSelectBox';
  // オプションを追加
  const options = [
    { value: value, text: '修正前' + '⭐'.repeat(value) },
    { value: '1', text: '⭐' },
    { value: '2', text: '⭐⭐' },
    { value: '3', text: '⭐⭐⭐' },
    { value: '4', text: '⭐⭐⭐⭐' },
    { value: '5', text: '⭐⭐⭐⭐⭐' }
  ];

  options.forEach(option => {
    const optionElement = document.createElement('option');
    optionElement.value = option.value;
    optionElement.textContent = option.text;
    selectElement.appendChild(optionElement);
  });

  // 作成した select 要素を表示
  const evaluationContainer = document.getElementById('evaluationContainer');
  evaluationContainer.appendChild(selectElement);
};

async function editSubmitButtonClick() {
  let data = {
    spot_name: document.getElementById('spot_name2').value,
    evaluation: document.getElementById('evaluationSelectBox').value,
    user_name: document.getElementById('user_name').value,
    created_at: document.getElementById('created_at').value,
    comment: document.getElementById('comment').value
  };
  const id = document.getElementById('spot_id').value;
  try {
    const response = await fetch(`/update/${id}`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        "X-CSRF-TOKEN": document.querySelector('input[name=_token]').value
      },
      body: JSON.stringify(data)
    });
    const result = await response.json();

    // 失敗時の処理
    if (!response.ok) {
      // バリデーションエラー（422）の場合
      if (response.status === 422 && result.errors) {
        let errorMessages = Object.values(result.errors).flat().join("\n");
        alert(errorMessages);
      } else {
        alert("エラーが発生しました。");
      }
      return; // 画面リロードしない
    }
    // 成功時の処理
    alert(result.message);
    window.location.href = '/'; // 成功時のみリダイレクト
  } catch (error) {
    //console.error('Error:', error);
  }
};

//修正状態の解除
function resetEditState() {
  document.getElementById("editButton").style.display = "block";
  document.getElementById("editSubmitButton").style.display = "none";
  document.getElementById("spot_name2").disabled = true;
  const evaluationDisplay = document.getElementById("evaluationDisplay");
  evaluationDisplay.style.display = 'block';
  document.getElementById("user_name").disabled = true;
  document.getElementById("created_at").disabled = true;
  document.getElementById("comment").disabled = true;

  // セレクトボックスを削除し、元の評価表示に戻す
  const evaluationContainer = document.getElementById('evaluationContainer');
  evaluationContainer.appendChild(evaluationDisplay); // 元の評価表示を復元
}
