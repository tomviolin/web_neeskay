<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>Neeeskay Paths</title>
    <script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
    <script src="https://code.jquery.com/ui/1.11.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
    <style>
      html, body, #map-canvas, #loading {
        height: 100%;
        margin: 0px;
        padding: 0px
      }
    #loading {
      position: absolute;
      width:100%;
    }
    #map-canvas {
      position: absolute;
      width: 100%;
    }
    </style>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAK_CE0qnjnedd0QUbXaYVLBzIVnQGCg1U"></script>
    <script>
$(function() {

$( "#dialog" ).dialog({
	modal:true,

});
window.setTimeout(theRest, 1000);
});


function theRest(){
  var ctaLayer = new google.maps.KmlLayer({
    url: '<?=$_GET["query"]?>'
  });
  console.log("boom");

  var map = new google.maps.Map(document.getElementById('map-canvas'));
  ctaLayer.setMap(map);

google.maps.event.addListener(ctaLayer, 'defaultviewport_changed', function() {
  console.log("boomlet");
  var chicago = new google.maps.LatLng(41.875696,-87.624207);
  var mapOptions = {
    zoom: 20,
    center: chicago
  }
  var bounds = ctaLayer.getDefaultViewport();
  map.fitBounds(bounds);
  $("#dialog").dialog('close');
});

google.maps.event.addListener(ctaLayer, 'click', function(){
	console.log('moused.');
});

}



    </script>
  </head>
  <body>
<div id="dialog" title="Basic dialog">
  <p><center><img src="gears.gif"></center></p>
</div>

    <div id="map-canvas"></div>
  </body>
</html>
