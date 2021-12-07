<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="style.css">
<title>Ship Data Monitor</title>
<!-- <script src="/jquery/ui/js/jquery-1.7.2.min.js"></script> -->
<script src="/jquery/ui/development-bundle/jquery-1.7.2.js"></script>
<script src="highcharts/stock/js/highstock.src.js"></script>
<script src="highcharts/stock/js/modules/exporting.js"></script>
<!--<script src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>-->
<script>
var UNDEFINED;
var chart;
var maxDepth;
var intervalid = 0;
var allDepths;
var allTemps;
var chartTooltipLastX;

$(document).ready(function() {
	/*
	Highcharts.setOptions({
		lang: {
			loading: 'Loading fine detail ... <br><br><img src="animated_gears.gif">'
		},
			global: { useUTC: false }
	});
	*/
	jQuery.getJSON('shipdata.php?callback=?', function(data) {
		var lines = [],
			ddate,
			depths = [],
			temperatures= [];

		maxDepth = data.maxdepth;
		$.each(data.data,function(i, line) {
			ddate = line[0];
			if (!isNaN(ddate)) {
				/*
				if (lastddate != -1 && ddate - lastddate > 60000*60*2) {
					depths.push([lastddate + 60000, null]);
					//bottom.push([lastddate + 60000, null]);
					temperatures.push([lastddate + 60000, null]);
					console.log("---null---");
				}
				*/
				if (line[1] != "") depths.push([ddate, parseFloat(line[1])]);
				if (line[2] != "") temperatures.push([ddate, parseFloat(line[2])]);
				lastddate = ddate;
				//console.log(line[0]+" "+line[1]+" "+line[2])
			}
		});
		/*
		function redo(e) {
			if (this.setExtremesLevel) {} else {this.setExtremesLevel = 0; }
			if (this.setExtremesLevel > 0) return true;
			this.setExtremesLevel ++;
			var retval = redo1(e);
			this.setExtremesLevel--;
			return retval;
		}

		function redo1(e) {

			var xAxismin = e.min;
			var xAxismax = e.max;
			if (xAxismin == e.currentTarget.min && xAxismax == e.currentTarget.max) {
				return false;
			}
			console.log(new Date(xAxismin).toString());
			console.log(new Date(xAxismax).toString());
			if (xAxismin > xAxismax) {
				xAxismin -= 1000*60*60*24*365;
				console.log(new Date(xAxismin).toString());
				console.log(new Date(xAxismax).toString());
				//e.min = xAxismin;
				intervalid = 1;
			}
			if (xAxismax - xAxismin > 60*60*48*1000) { 
				if (intervalid == 2) {
				//	return true;
				}
				chart.showLoading();
				chart.series[0].hide();
				chart.series[1].hide();
				chart.series[2].hide();
				chart.series[0].setData(allDepths);
				chart.series[1].setData(allDepths);
				chart.series[2].setData(allTemps);
				chart.series[0].show();
				chart.series[1].show();
				chart.series[2].show();
				chart.hideLoading();
				intervalid = 2;
				return true;
			}
			console.log('range:'+Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', xAxismin)+" - "
					+Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', xAxismax));
			url = "shipdata.php?startdate="+xAxismin+"&enddate="+xAxismax;
			chart.showLoading();
			console.log("getting: "+url);
			mm=jQuery.get(url, null, function(csv,state,xhr) {
				var lines = [],
					ddate,
					depths=[],
					temperatures=[];
				console.log('we got data!');
				if (typeof csv !== 'string') {
					csv = xhr.responseText;
				}
				csv = csv.split(/\n/g);
				var lastddate = -1;
				$.each(csv,function(i, line) {
					if (i == 0) {
						maxDepth = parseFloat(line)*1.05;
						return;
					}
					if (line.substr(0,1) != '"') {
						line = line.split(/,/);
						ddate = Date.parse(line[0] + ' UTC');
						if (!isNaN(ddate)) {
							
							//if (lastddate != -1 && ddate - lastddate > 60000*60*2) {
							//	depths.push([lastddate + 60000, null]);
							//	//bottom.push([lastddate + 60000, null]);
							//	temperatures.push([lastddate + 60000, null]);
							//	console.log("---null---");
							//}
							
							if (line[1] != "") depths.push([ddate, parseFloat(line[1])]);
							if (line[2] != "") temperatures.push([ddate, parseFloat(line[2])]);
							lastddate = ddate;
						}
					}
				});
				console.log(depths);
				console.log(temperatures);
				console.log(xAxismin+" - "+xAxismax);
				chart.series[0].setData(depths);//,false, false);
				chart.series[1].setData(depths);//,false,false);
				chart.series[2].setData(temperatures);//,false,false);
				intervalid = 1;
				chart.hideLoading();
			});
			console.log("got.");
			console.log(mm);
			return true;
		}
		*/
		chartOptions = {
			chart: {
				renderTo: "chart_container",
				zoomType: "x",
				spacingRight: 20,
				alignTicks: false//,
				//events: {
					//tooltipRefresh: function(e) {
					//	doGoogleMap(chartTooltipLastX);
					//}
				//}
			},
			rangeSelector: {
				buttons: [
				{
					type: 'day',
					count: 1,
					text: '1d'
				}, {
					type: 'month',
					count: 1,
					text: '1m'
				}, {
					type: 'month',
					count: 3,
					text: '3m'
				}, {
					type: 'month',
					count: 6,
					text: '6m'
				}, {
					type: 'month',
					count: 12,
					text: '1y'
				}, {
					type: 'all',
					text: 'All'
				}]
				
			},
			loading: {
				style: {
					opacity: 0.9
				}
			},
			navigator: { 
				series: { data: depths, threshold:0}, yAxis: {reversed:true },
				maskFill: 'rgba(255,255,255,0.8)'
			},
			title: {
				text: "Ship Data Monitoring"
			},
			//tooltip: {
			//	useHTML: true,
			//	formatter: function() {
			//		var depth,temp;
			//		if (this.points && this.points.length >= 0 && this.points[0].y) {
			//			depth = this.points[0].y;
			//		}
			//		if (this.points && this.points.length >= 1 && this.points[1] && this.points[1].y) {
			//			temp = this.points[1].y;
			//		}

			//		var s = "<b>"+Highcharts.dateFormat('%m-%d-%Y %H:%I:%S',this.x)+'</b>'
			//			+"<br>Depth: " + Highcharts.numberFormat(depth,2) + "m"
			//			+"<br>Temp: " + Highcharts.numberFormat(temp,2) + " C";
					//chartTooltipLastX = this.x;
			//		return s;
			//	}
			//},
			subtitle: {
				text: document.ontouchstart == undefined ?
					"Click and drag in plot area to zoom in" :
					"Drag finger over plot area to zoom in"
			},
			xAxis: {
				type: 'datetime',
				minRange: 1000*60*2,	// 2 minutes
				startOnTick: false,
				ordinal: false//,
				//events: {
					//setExtremes: redo
				//}
			},
			yAxis: [{
				// [0] depth 
				labels: {align: "right", x: -3, y:4},
				reversed: true,
				min: 0.0,
				//max: maxDepth,
				title: {
					text: 'Depth (m)',
				},
				tickLength: 0,
				lineColor: '#',
				tickColor: '#232323',
				gridLineColor: '#d3d3d3',
				tickInterval: 20.0,
				allowDecimals: false,
				tickWidth: 1,
				tickmarkPlacement: 'between',
				startOnTick: false,
				endOnTick: false,
				maxPadding: 0
			},{
				// [1] temperature
				min: 0.0,
				// max:30,
				title: {
					text: 'Surface Temperature (C)',
				},
				tickLength: 0,
				lineColor: '#',
				opposite: true,
				tickColor: '#232323',
				gridLineColor: '#d3d3d3',
				allowDecimals: false,
				tickWidth: 1,
				gridLineWidth: 0,
				tickmarkPlacement: 'between'
			}],
			series: [
			{
				// bottom
				id: "bottom",
				type: "area",
				color: '#613318',
				//fillColor: '#613318',
				fillOpacity: 1,
				lineWidth: 0,
				marker: { enabled: false },
				yAxis: 0,
				showInLegend: false,
				threshold: 150, //maxDepth,
				name: "Depth"
			}
			,
			{
				// depth
				id: "depth",
				type: "area",
				color: '#80f0ff',
				fillColor: {
					linearGradient: [0, 0, 0, 300],
					stops: [
						[0, 'rgba(16, 100, 127,0.1)'],
						[1, 'rgba(64 ,120, 127,1)']
					]
				},
				fillOpacity: 0.9,
				lineWidth: 0,
				marker: { enabled: false },
				yAxis: 0,
				enableMouseTracking: false,
				threshold: 0
			},
			{
				// temperature
				id: "temperature",
				type: "line",
				yAxis: 1,
				stacking: null,
				marker: { enabled: false },
				name: "Sfc. Temp",
				color: "red"
			}],
			plotOptions: {
				series: {
					animation: false,
					gapSize: 2,
					dataGrouping: {
						enabled: true,
						//forced: true,
						smoothed: true
					},
					connectNulls: true
				}
			}
		};

		chartOptions.series[0].data = depths;
		chartOptions.series[1].data = depths;
		chartOptions.series[2].data = temperatures;
		allTemps = temperatures;
		allDepths = depths;
		intervalid = 2;
		chart = new Highcharts.StockChart(chartOptions);
		//initmap();
	});
});

function initmapxxx(){
	// init google map
	var mylatlng = new google.maps.LatLng(43,-87.9);
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
	map = new google.maps.Map(document.getElementById('mapcontainerframe').contentDocument.getElementById('map_container'), mapOptions);

}

/*
var mapRangeMin, mapRangeMax;
var mapMarker;
var mapPolyline;
var courseCoords;
var courseTimes;
var mapTimer = UNDEFINED;
var doGoogleMap = function(xvalue) {
	if (mapTimer != UNDEFINED) {
		// timer still active - reset
		clearTimeout(mapTimer);
		mapTimer = UNDEFINED;
	}
	mapTimer = setTimeout("doGoogleMapReal("+xvalue+")", 500);
	return true;
};

var doGoogleMapReal=function(xvalue) {
	clearTimeout(mapTimer);
	mapTimer = UNDEFINED;
	console.log("doing google map...");
	if (mapRangeMin == UNDEFINED || mapRangeMax == UNDEFINED || xvalue < mapRangeMin || xvalue > mapRangeMax) {
		// must (re)load data
		$.getJSON("dotrackdata.php?date="+xvalue+"&callback=?",function(data) {
			if (data.path.length == 0) return true;
			courseCoords = [];
			courseTimes = [];
			var markerPos = new google.maps.LatLng(data.marker[0],data.marker[1]);
			var bb = new google.maps.LatLngBounds(markerPos,markerPos);
			if (data.path.length > 0) {
				mapRangeMin = data.path[0][0];
				mapRangeMax = data.path[data.path.length-1][0];
				console.log("map range established: "+mapRangeMin+"-"+mapRangeMax);
			}
			for (var i = 0; i < data.path.length; ++i) {
				var p=new google.maps.LatLng(data.path[i][1],data.path[i][2]);
				courseCoords.push(p);
				bb.extend(p);
				courseTimes.push(data.path[i][0]);
			}
			// update marker
			if (mapMarker && mapMarker.setMap) {
				mapMarker.setPosition(p);
			} else {
				mapMarker = new google.maps.Marker({
					position: p,
					map: map
				});
			}

			//update polyline
			if (mapPolyline && mapPolyline.setMap) {
				mapPolyline.setPath(courseCoords);
			} else {
				mapPolyline = new google.maps.Polyline({
					clickable: false,
					editable: false,
					map:map,
					path: courseCoords,
					strokeColor: '#0000FF',
					strokeOpacity: 0.5,
					strokeWeight: 3
				});
			}
			map.fitBounds(bb);
		});
	} else if (xvalue >= mapRangeMin && xvalue <= mapRangeMax) {
		console.log("xvalue of "+xvalue+" is within range of "+mapRangeMin+"-"+mapRangeMax);
		for (i = 0; i < courseCoords.length; ++i) {
			if (Math.abs(courseTimes[i] - xvalue) < 120000) {
				var p=courseCoords[i];
			}
			var bb = map.getBounds();
			bb.extend(courseCoords[i]);
			map.fitBounds(bb);
			if (mapMarker && mapMarker.setPosition) {
				mapMarker.setPosition(p);
			} else {
				mapMarker = new google.maps.Marker({
					position:p,
					map:map
				});
			}
		}
	}
	return true;
};
*/
</script>
	
</head>
<body>
<div id="chart_container" style="min-width: 400px; height: 400px; margin: 0 auto"></div>
<iframe id="mapcontainerframe" style="border:0px;margin:0px;padding:0px;" src="mapcontainerframe.html"></iframe>
<button onclick="document.getElementById('mapcontainerframe').contentWindow.initmap()">turn on map</button>
</body>
</html>
