<?php
mysql_connect("waterdata.glwi.uwm.edu","shipuser","arrrrr");
mysql_select_db("neeskay");
$date = floor($_GET['date']/1000);
$query = "select distinct concat(mid(recdate, 1, 16),':00') as recminute from trackingfast where recdate between convert_tz(mid(convert_tz('".date('Y-m-d H:i:s',$date)."', 'GMT','SYSTEM'),1,10),'SYSTEM','GMT') AND convert_tz(mid(convert_tz('".date('Y-m-d H:i:s',($date+86400))."', 'GMT','SYSTEM'),1,10),'SYSTEM','GMT')";
//echo $query;
$query = "create temporary table recminq $query";
$result = mysql_query($query);
$query2 = "select recdate,gpslat,gpslng from trackingfast, recminq where trackingfast.recdate = recminq.recminute order by recminq.recminute";
$result = mysql_query($query2);
//echo mysql_error();
$count = 0;
$clat="";
$clng="";
$points = array();
$totlat = 0;
$totlng = 0;
while ($row=mysql_fetch_array($result)) {
//	echo $row['gpslat'].",".$row['gpslng']." => ".strtotime($row['recdate'].' UTC').",".$date."<br>\n";
	if (strtotime($row['recdate']. ' UTC') - $date <120) {
		$marker = array($row['gpslat'],$row['gpslng']);
	}
	if ($row['gpslat'] > 0 && $row['gpslng'] < 0) {
		$pass = FALSE;
		if ($clat == "" && $clng == "") {
			$pass = TRUE;
		} else {
			$latdiff = pow(($row['gpslat'] - $clat),2);
			$lngdiff = pow(($row['gpslng'] - $clng),2);
			if (($latdiff + $lngdiff) > 0.00002) {
				$pass = TRUE;
			}
		}
		if ($pass) {
			$track .= "|".round($row['gpslat'],3).",".round($row['gpslng'],3);
			$clat = $row['gpslat'];
			$clng = $row['gpslng'];
			$points[] = array($clat,$clng);
			$totlat += $clat;
			$totlng += $clng;
			$count++;
		}
	}
	//if ($count > 20) break;
	//echo $row[recdate].",".$row['gpslat'].",".$row['gpslng']."<br>\n";
}
$avglat = $totlat / $count;
$avglng = $totlng / $count;

file_put_contents("x",$_GET['date']."\n".$track);
// header("Status: 302 Moved");
// header("Location: http://maps.google.com/maps/api/staticmap?markers=$marker&path=color:0x0000FF80|weight:5$track&size=150x150&sensor=false");
?>
<html>
<head>
<script src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>
<script>
function initmap() {
	// dom ready...
	var mylatlng = new google.maps.LatLng(<?="$marker[0],$marker[1]"?>);
	var mapOptions = {
		zoom: 10,
		center: mylatlng,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		mapTypeControl: false,
		mapTypeControlOptions: {
			mapTypeIds: []
		},
		streetViewControl: false
	};
	map = new google.maps.Map(document.getElementById('map_container'), mapOptions);
	var shipCourseCoordinates = [
<?= $comma=""; foreach ($points as $point) {
	echo "$comma\t\tnew google.maps.LatLng({$point[0]},{$point[1]})";
	$comma=",\n";
} echo "\n";?>
	];
	var shipCourse = new google.maps.Polyline({
		path: shipCourseCoordinates,
		strokeColor: '#0000FF',
		strokeOpacity: 0.5,
		strokeWeight: 2
	});
	shipCourse.setMap(map);
	var shippos = new google.maps.Marker({
		position: mylatlng,
		map: map
	});
}

google.maps.event.addDomListener(window, 'load', initmap);

</script>
<style type="text/css">
	body, #map_container {
		padding:0; margin:0;
	}
</style>

</head>
<body>
<div id="map_container" style="height:200px;width:200px;"></div>
</body>
</html>
