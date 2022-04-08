<html>
  <head>
    <meta charset="utf-8" />
    <title>Display a feature layer</title>
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no" />
<link href="https://api.mapbox.com/mapbox-gl-js/v2.7.0/mapbox-gl.css" rel="stylesheet">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" crossorigin=""></script>

    <!-- Load Esri Leaflet from CDN -->
    <script src="https://unpkg.com/esri-leaflet@3.0.4/dist/esri-leaflet.js"></script>

    <!-- Load Esri Leaflet Vector from CDN -->
    <script src="https://unpkg.com/esri-leaflet-vector@3.1.1/dist/esri-leaflet-vector.js" crossorigin=""></script>
    <script src='//api.tiles.mapbox.com/mapbox.js/plugins/leaflet-omnivore/v0.3.1/leaflet-omnivore.min.js'></script>

    <style>
      html,
      body,
      #map {
        padding: 0;
        margin: 0;
        height: 100%;
        width: 100%;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 14px;
        color: #323232;
      }
    </style>
  </head>
  <body>
    <div id="map"></div>
    <script>
      const apiKey = 'AAPK118c7c0364fa4520881fac839977796d02vkh7rm8BrbWUSHYHq9PdwSYJCsm8gqHm_N6qwrARsRagMyZyQ_P3-dNobtjwvQ';

      const map = L.map("map").setView([43.018116, -87.903688], 19);




  var vectorTiles = {};
  var allEnums = [
    'ArcGIS:Imagery',
    'ArcGIS:Imagery:Standard',
    'ArcGIS:Imagery:Labels',
    'ArcGIS:LightGray',
    'ArcGIS:LightGray:Base',
    'ArcGIS:LightGray:Labels',
    'ArcGIS:DarkGray',
    'ArcGIS:DarkGray:Base',
    'ArcGIS:DarkGray:Labels',
    'ArcGIS:Navigation',
    'ArcGIS:NavigationNight',
    'ArcGIS:Streets',
    'ArcGIS:StreetsNight',
    'ArcGIS:StreetsRelief',
    'ArcGIS:StreetsRelief:Base',
    'ArcGIS:Topographic',
    'ArcGIS:Topographic:Base',
    'ArcGIS:Oceans',
    'ArcGIS:Oceans:Base',
    'ArcGIS:Oceans:Labels',
    'OSM:Standard',
    'OSM:StandardRelief',
    'OSM:StandardRelief:Base',
    'OSM:Streets',
    'OSM:StreetsRelief',
    'OSM:StreetsRelief:Base',
    'OSM:LightGray',
    'OSM:LightGray:Base',
    'OSM:LightGray:Labels',
    'OSM:DarkGray',
    'OSM-DarkGray:Base',
    'OSM-DarkGray:Labels',
    'ArcGIS:Terrain',
    'ArcGIS:Terrain:Base',
    'ArcGIS:Terrain:Detail',
    'ArcGIS:Community',
    'ArcGIS:ChartedTerritory',
    'ArcGIS:ChartedTerritory:Base',
    'ArcGIS:ColoredPencil',
    'ArcGIS:Nova',
    'ArcGIS:ModernAntique',
    'ArcGIS:ModernAntique:Base',
    'ArcGIS:Midcentury',
    'ArcGIS:Newspaper',
    'ArcGIS:Hillshade:Light',
    'ArcGIS:Hillshade:Dark'
  ];

  // the L.esri.Vector.vectorBasemapLayer basemap enum defaults to 'ArcGIS:Streets' if omitted
  vectorTiles.Default = L.esri.Vector.vectorBasemapLayer(null, {
    apiKey
  });
  allEnums.forEach((enumString) => {
    vectorTiles[
      enumString
    ] = L.esri.Vector.vectorBasemapLayer(enumString, {
      apiKey
    });
  });

  L.control
    .layers(vectorTiles, null, {
      collapsed: false
    })
    .addTo(map);

  vectorTiles.Default.addTo(map);

/*
      L.esri.Vector.vectorBasemapLayer("ArcGIS:Imagery", {
        apikey:  apiKey
      }).addTo(map);

 */

      var runLayer = omnivore.kml('<?= $_GET["query"]?>')
      .on('ready', function() {
         map.fitBounds(runLayer.getBounds());
      }).addTo(map);

   runLayer.eachLayer(function (layer) {
          if (layer.feature.geometry.type === 'LineString') {
            // See Leaflet path layers options
            // http://leafletjs.com/reference-1.0.3.html#path
            layer.setStyle({
              color: '#FFFF00',
              weight: 9 
            });
          } else {
            console.log('unknown geometry type');
          }
      });

    </script>
  </body>
</html>
