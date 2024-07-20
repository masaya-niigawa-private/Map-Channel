<!DOCTYPE html>
<html>
<head>
  <title>Marker Clustering</title>
  <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
  <script src="https://unpkg.com/@googlemaps/markerclusterer@2.5.3/dist/index.min.js"></script>
  <style>
    /* Ensure the map takes up the entire viewport */
    #map {
      height: 100vh;
      width: 100%;
    }
  </style>
</head>
<body>
  <div id="map"></div>
  <script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC2ESS8ztDAAxpYZDfxulply5HeSti6cNA&callback=initMap&v=weekly"
    defer
  ></script>
  <script src="/js/test.js"></script>
</body>
</html>
