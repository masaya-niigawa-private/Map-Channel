<!DOCTYPE html>
<html lang="ja">

  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>喫煙所まっぷ</title>

    <script src="/js/geolocation.js" defer></script>
    <script async defer
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC2ESS8ztDAAxpYZDfxulply5HeSti6cNA&callback=initMap"></script>
  </head>

  <body>
    <div id="map" style="width: 100%; height: 500px"></div>
    <button onclick="getNow()">現在位置</button>
  </body>

</html>