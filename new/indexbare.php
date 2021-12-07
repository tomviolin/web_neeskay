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
var chart;

$(document).ready(function() {
	jQuery.getJSON('http://waterbase.uwm.edu/neeskay/highchartsdemo.php?callback=?', function(data) {
		var	ddate,
			depths = [],
			temperatures= [];

		$.each(data.data,function(i, line) {
			ddate = line[0];
			if (!isNaN(ddate)) {
				if (line[1] != "") depths.push([ddate, parseFloat(line[1])]);
				if (line[2] != "") temperatures.push([ddate, parseFloat(line[2])]);
				lastddate = ddate;
				//console.log(line[0]+" "+line[1]+" "+line[2])
			}
		});
		chartOptions = {
			chart: {
				renderTo: "chart_container",
				zoomType: "x",
				spacingRight: 20,
				alignTicks: false
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
			},
			yAxis: [{
				// [0] depth 
				labels: {align: "right", x: -3, y:4},
				reversed: true,
				min: 0.0,
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
				fillOpacity: 1,
				lineWidth: 0,
				marker: { enabled: false },
				yAxis: 0,
				showInLegend: false,
				threshold: 150, 
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
					}
				}
			}
		};

		chartOptions.series[0].data = depths;
		chartOptions.series[1].data = depths;
		chartOptions.series[2].data = temperatures;
		chart = new Highcharts.StockChart(chartOptions);
	});
});

</script>
	
</head>
<body>
<div id="chart_container" style="min-width: 400px; height: 400px; margin: 0 auto"></div>
<iframe id="mapcontainerframe" style="border:0px;margin:0px;padding:0px;" src="mapcontainerframe.html"></iframe>
<button onclick="document.getElementById('mapcontainerframe').contentWindow.initmap()">turn on map</button>
</body>
</html>
