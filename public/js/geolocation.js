let map;
let latitude;
let longitude;
let latlng;
let placesService;
let isEditButtonClicked = false;//フラグ
let nearbySpots = [];
let currentNearbyIdx = 0;

// 初期表示時に現在地を表示する
async function initMap_allCategory() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(onGetPositionSuccess, onGetPositionError);
  } else {
    alert("このブラウザは位置情報に対応していません。");
  }
}

// 現在地取得成功時のコールバック
function onGetPositionSuccess(position) {
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
// 現在地取得してマップを移動
// function moveToCurrentLocation() {
//   if (navigator.geolocation) {
//     navigator.geolocation.getCurrentPosition(
//       position => {
//         const currentLatLng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
//         map.setCenter(currentLatLng);
//         new google.maps.Marker({
//           position: currentLatLng,
//           map: map,
//           title: "現在地"
//         });
//       },
//       () => {
//         alert("現在地の取得に失敗しました。");
//       }
//     );
//   } else {
//     alert("このブラウザは位置情報に対応していません。");
//   }
// }

// マップ初期表示
async function initMap_kandai() {
  //関大の正門
  latlng = new google.maps.LatLng(34.773476, 135.508861);
  map = new google.maps.Map(document.getElementById("map"), {
    zoom: 17,
    tilt: 45,
    heading: 270,
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
  //正門がマップ上側になるように方角設定
  map.setHeading(270);

  // Places Serviceを初期化
  placesService = new google.maps.places.PlacesService(map);

  // 既存スポットのマーカーを生成
  addExistingMarkers(map);

  // クリック地点のマーカーを設定
  setupClickListener(map);
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

      const lat = marker.getPosition().lat();
      const lng = marker.getPosition().lng();

      // 近くのスポットをAPIで取得
      try {
        const response = await fetch(`/api/spots/nearby?lat=${lat}&lng=${lng}`);
        const data = await response.json();

        if (!data.length) {
          alert('近くにスポットはありません');
          return;
        }
        // 取得したスポットを配列に保存
        nearbySpots = data;
        currentNearbyIdx = 0;
        // 最初の1件を既存の詳細表示UIで描画
        showNearbySpotDetail(nearbySpots[currentNearbyIdx]);
      } catch (e) {
        alert('近隣スポットの取得に失敗しました');
      }

      //修正状態の場合はリセット
      if (isEditButtonClicked) {
        resetEditState();
      }
      document.getElementById('spot_id').value = (spotData[i].id);
      document.getElementById('category').value = (spotData[i].category);
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

        const popupImage = document.getElementById('popup-image');
        const mainImage = document.getElementById('main-image');

        if (photos.length > 0) {
          popupImage.innerHTML = '';
          mainImage.innerHTML = '';
          popupImage.src = "https://mapappp.s3.ap-northeast-3.amazonaws.com/" + photos[0].photo_path;

          const leftCol = document.createElement('div');
          const rightColWrapper = document.createElement('div');
          const rightCol = document.createElement('div');

          leftCol.className = 'left-column';
          rightColWrapper.className = 'right-scroll';
          rightCol.className = 'right-column';

          rightColWrapper.appendChild(rightCol);
          mainImage.appendChild(leftCol);
          mainImage.appendChild(rightColWrapper);

          photos.forEach((photo, index) => {
            const img = document.createElement('img');
            img.src = "https://mapappp.s3.ap-northeast-3.amazonaws.com/" + photo.photo_path;
            img.alt = 'Photo ' + (index + 1);

            if (index === 0) {
              img.className = 'large';
              leftCol.appendChild(img);
            } else {
              img.className = 'small';
              rightCol.appendChild(img);
            }
            //画像クリック時の削除イベント（※修正フラグがTRUEの場合に発動する）
            img.addEventListener('click', async () => {
              if (!isEditButtonClicked) return;
              const confirmDelete = confirm('この画像を削除しますか？');
              if (confirmDelete) {
                try {
                  const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                  const deleteRes = await fetch(`/photos/${photo.id}`, {
                    method: 'DELETE',
                    headers: {
                      'X-CSRF-TOKEN': token, // ← これが必要
                      'Accept': 'application/json',
                    },
                  });
                  if (deleteRes.ok) {
                    img.remove(); // 表示から削除
                    alert('画像を削除しました。');
                  } else {
                    alert('削除に失敗しました。');
                  }
                } catch (err) {
                  alert('エラーが発生しました: ' + err.message);
                }
              }
            });
          });
        } else {
          popupImage.removeAttribute('src');
          mainImage.innerHTML = "";
        }
      } catch (error) {
        alert(error.message);
      }

      //スレッドコメント検索
      try {
        const postContainer = document.getElementById('postContainer');
        postContainer.innerHTML = '';
        const response = await fetch(`/posts/${id}`);
        const posts = await response.json();
        if (posts.length > 0) {
          posts.forEach(post => {
            const postElement = document.createElement('div');
            postElement.innerHTML = `
                <p><strong>${post.author || '名無し'}　</strong>${formatDate(post.created_at)}</p>
                <p>${post.content}</p>
                <hr>
            `;
            postContainer.appendChild(postElement);
          });
        }
      } catch {

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
  new markerClusterer.MarkerClusterer({ map, markers });
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
window.initMap_kandai = initMap_kandai;
window.initMap_allCategory = initMap_allCategory;

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

// 修正ボタンクリック
function editButtonClick() {
  isEditButtonClicked = true;
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

//修正完了ボタンクリック
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
    if (result.redirect_url) {
      window.location.href = result.redirect_url;
    }
  } catch (error) {
    //console.error('Error:', error);
  }
};

//修正状態の解除
function resetEditState() {
  isEditButtonClicked = false;
  document.getElementById("editButton").style.display = "block";
  document.getElementById("editSubmitButton").style.display = "none";
  document.getElementById("spot_name2").disabled = true;
  const evaluationDisplay = document.getElementById("evaluationDisplay");
  evaluationDisplay.style.display = 'block';
  document.getElementById("user_name").disabled = true;
  document.getElementById("created_at").disabled = true;
  document.getElementById("comment").disabled = true;
  document.getElementById('evaluationSelectBox').style.display = 'none';
}

//日付フォーマット
const formatDate = (dateString) => {
  const date = new Date(dateString);
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0'); // 月を2桁に
  const day = String(date.getDate()).padStart(2, '0'); // 日を2桁に
  return `${year}/${month}/${day}`;
};

//「次へ」「前へ」ボタン
document.addEventListener('DOMContentLoaded', function () {
  const nextBtn = document.getElementById('next-spot-btn');
  const prevBtn = document.getElementById('prev-spot-btn');

  if (nextBtn) {
    nextBtn.addEventListener('click', function () {
      if (nearbySpots.length && currentNearbyIdx < nearbySpots.length - 1) {
        currentNearbyIdx++;
        showNearbySpotDetail(nearbySpots[currentNearbyIdx]);
      }
    });
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', function () {
      if (nearbySpots.length && currentNearbyIdx > 0) {
        currentNearbyIdx--;
        showNearbySpotDetail(nearbySpots[currentNearbyIdx]);
      }
    });
  }
});

//画面描画用メソッド
async function showNearbySpotDetail(spot) {
  //修正状態の場合はリセット
  if (isEditButtonClicked) {
    resetEditState();
  }
  document.getElementById('spot_id').value = spot.id;
  document.getElementById('category').value = spot.category;
  document.getElementById('spot_name1').value = spot.spot_name;
  document.getElementById('spot_name2').value = spot.spot_name;
  document.getElementById('evaluationDisplay').value = '★'.repeat(spot.evaluation);
  document.getElementById('user_name').value = (spot.user_name);
  const createdAtJST = new Date(spot.created_at);
  document.getElementById('created_at').value = createdAtJST.toLocaleDateString('ja-JP');

  const id = spot.id;
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

    const popupImage = document.getElementById('popup-image');
    const mainImage = document.getElementById('main-image');

    if (photos.length > 0) {
      popupImage.innerHTML = '';
      mainImage.innerHTML = '';
      popupImage.src = "https://mapappp.s3.ap-northeast-3.amazonaws.com/" + photos[0].photo_path;

      const leftCol = document.createElement('div');
      const rightColWrapper = document.createElement('div');
      const rightCol = document.createElement('div');

      leftCol.className = 'left-column';
      rightColWrapper.className = 'right-scroll';
      rightCol.className = 'right-column';

      rightColWrapper.appendChild(rightCol);
      mainImage.appendChild(leftCol);
      mainImage.appendChild(rightColWrapper);

      photos.forEach((photo, index) => {
        const img = document.createElement('img');
        img.src = "https://mapappp.s3.ap-northeast-3.amazonaws.com/" + photo.photo_path;
        img.alt = 'Photo ' + (index + 1);

        if (index === 0) {
          img.className = 'large';
          leftCol.appendChild(img);
        } else {
          img.className = 'small';
          rightCol.appendChild(img);
        }
        //画像クリック時の削除イベント（※修正フラグがTRUEの場合に発動する）
        img.addEventListener('click', async () => {
          if (!isEditButtonClicked) return;
          const confirmDelete = confirm('この画像を削除しますか？');
          if (confirmDelete) {
            try {
              const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
              const deleteRes = await fetch(`/photos/${photo.id}`, {
                method: 'DELETE',
                headers: {
                  'X-CSRF-TOKEN': token, // ← これが必要
                  'Accept': 'application/json',
                },
              });
              if (deleteRes.ok) {
                img.remove(); // 表示から削除
                alert('画像を削除しました。');
              } else {
                alert('削除に失敗しました。');
              }
            } catch (err) {
              alert('エラーが発生しました: ' + err.message);
            }
          }
        });
      });
    } else {
      popupImage.removeAttribute('src');
      mainImage.innerHTML = "";
    }
  } catch (error) {
    alert(error.message);
  }

  //スレッドコメント検索
  try {
    const postContainer = document.getElementById('postContainer');
    postContainer.innerHTML = '';
    const response = await fetch(`/posts/${id}`);
    const posts = response.json();
    if (posts.length > 0) {
      posts.forEach(post => {
        const postElement = document.createElement('div');
        postElement.innerHTML = `
                <p><strong>${post.author || '名無し'}　</strong>${formatDate(post.created_at)}</p>
                <p>${post.content}</p>
                <hr>
            `;
        postContainer.appendChild(postElement);
      });
    }
  } catch {

  }

  //詳細ポップアップ表示（11/14追加）
  const syosai = document.querySelector('.syosai');
  syosai.showModal();

  //修正ボタン表示
  document.getElementById("editButton").style.display = "block";
}