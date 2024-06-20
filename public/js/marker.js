// マーカー生成
//引数：DBから取得したデータ
// function setSpotMarkers() {
//   let markers = [];
//   for (var i = 0; i < JS_SPOTS.length; i++) {
//     position = { lat: parseFloat(JS_SPOTS[i]['ido']), lng: parseFloat(JS_SPOTS[i]['keido']) };
//     markerLatLng = new google.maps.LatLng(position);
//     markers[i] = new google.maps.Marker({
//       position: markerLatLng,
//       map: map
//     });
//     //マーカーのアイコンを変える
//     // markers[i].setOptions({
//     //   icon: {
//     //     url: '/img/map_logo.png'
//     //   }
//     // });
//   }
// };

// function setSpotMarkers() {
//     position = { lat: parseFloat(JS_SPOTS[1]['ido']), lng: parseFloat(JS_SPOTS[1]['keido']) };
//     markerLatLng = new google.maps.LatLng(position);
//     markers = new google.maps.Marker({
//       position: markerLatLng,
//       map: map
//     });
// };

// function test(){
//   console.log(JS_SPOTS);
  
// }

// プロトタイプ
function setMarkers() {

const spots = spotData;//sopotData -> グローバル変数
  for(let spot in spots){
    position = { lat: spot.ido, lng: spot.keido };
    markerLatLng = new google.maps.LatLng(position);
    markers = new google.maps.Marker({
      position: markerLatLng,
      map: map
    });
  }

}
  


//window.console.log(JS_SPOTS);
window.setMarkers();
// window.test();