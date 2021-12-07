<?php
$TZ = 'America/Chicago';
define('TTF_DIR','/usr/share/fonts/truetype/msttcorefonts/');
require_once("jpgraph-current/src/jpgraph.php");
require_once("jpgraph-current/src/jpgraph_line.php");
require_once("jpgraph-current/src/jpgraph_date.php");
require_once("localreround.php");

global $dateformat;


$leftmargin = 55;
$rightmargin = 55;
$topmargin = 30;
$botmargin = 130;

function DateTimeCallback ($label) {
	global $dateformat;
	return date($dateformat, $label);
}

function DepthCallback($label) {
	return - $label;
}

$con = mysqli_connect("waterdata.glwi.uwm.edu","shipuser","arrrrr");
mysqli_select_db ($con, "neeskay");

// **** Process the arguments ****
// ** start time **
$starttime = $_REQUEST['starttime'];
if ($starttime == '') {
	$starttime = date('Y-m-d H:i:s', time()-60*60*36);
} else {
	$starttime = date('Y-m-d H:i:s', strtotime($starttime));
}

// ** end time **
$endtime = $_REQUEST['endtime'];
if ($endtime == '') {
	$endtime = date('Y-m-d H:i:s', time());
} else {
	$endtime = date('Y-m-d H:i:s', strtotime($endtime));
}

// ** skip **
$skip = $_REQUEST['skip'];



// ** width **
$width = $_REQUEST['width'];
if ($width == '' || $width == 0) {
	$width=900;
}

// ** height //
$height = $_REQUEST['height'];
if ($height == '' || $height == 0) {
	$height = 600;
}

// ** date format **
if ($_REQUEST['dateformat'] == "short") {
	$dateformat = "H:i:s";
	$botmargin = 60;
} else if ($_REQUEST['dateformat'] == "medium") {
	$dateformat = 'm-d  H:i ';
	$botmargin=77;
} else {
	$dateformat = 'Y-m-d H:i:s ';
	$botmargin = 130;
}

// ** title **
if ($_REQUEST['title'] == "") {
	$graphtitle = "Temperature/Depth: Cruise 09/21/2006";
} else {
	$graphtitle = $_REQUEST['title'];
}

// ** refresh **
if ($_REQUEST['refresh'] > 0) {
	header("Refresh: ".$_REQUEST['refresh']+0);
}
if ($_REQUEST['debug']=='y') { echo "starttime=$starttime, endtime=$endtime<br>\n"; }

// convert starttime and endtime to GMT using mysql
$query = "select convert_tz('".$starttime."','$TZ','GMT') as starttime_gmt, convert_tz('".$endtime."','$TZ','GMT') as endtime_gmt";
if ($_REQUEST['debug']=='y') { echo "query='$query'<br>\n"; }
$result = mysqli_query($con, $query);
$trow = mysqli_fetch_array($result);
$starttime_gmt = $trow['starttime_gmt'];
$endtime_gmt   = $trow['endtime_gmt'];

$result = mysqli_query($con, "select count(*) as reccount,max(if(tempc>0 and tempc<100,tempc,null)) as maxtemp, max(depthm) as maxdepthm from trackingdata_flex where recdate >= '".$starttime_gmt."' and recdate <= '".$endtime_gmt."' order by recdate");
$row = mysqli_fetch_array($result);
$numrows = $row["reccount"];
$maxtemp = $row["maxtemp"];
$maxdepthm=$row["maxdepthm"];
//echo "reccount=$numrows<br>\n";
if ($skip == '' || $skip == 0) {
	$skip = floor($numrows / ($width - 80) );
}
if ($skip < 1 || $_REQUEST['csvdownload'] == "yes") {
	$skip = 1;
}
if ($_REQUEST['reallyskip'] > 0) {
	$skip = $_REQUEST['reallyskip'];
}
// echo "numrows=$numrows, skip=$skip";
mysqli_free_result($result);

mysqli_query($con,"set @id := 0");

# $query=("select convert_tz(recdate,'GMT','$TZ') as lrecdate, trackingdata_flex.*, (@id := @id + 1) as recidinc from trackingdata where recdate >= '".$starttime_gmt."' and recdate <= '".$endtime_gmt."' and (@id := @id + 1) mod ".($skip+1)." = 0 order by recdate");

if ($_REQUEST['csvdownload'] == "yes" && $_REQUEST['path'] == '') {
	// CSV download requested
	$query=("select convert_tz(recdate,'GMT','$TZ') as lrecdate, trackingdata_flex.* from trackingdata_flex where recdate >= '".$starttime_gmt."' and recdate <= '".$endtime_gmt."' /* and recordid mod $skip = 0 */ order by recdate");
	if ($_REQUEST['debug']=='y') {
		echo "query='$query'\n";
	}
	$result = mysqli_query($con,$query);
	if (mysqli_errno($con) > 0) {
		echo __LINE__.": ".mysqli_error($con);
		exit;
	}
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=shipdata".substr($starttime,0,10).".csv");
	
	$currentysilayout = "x";
	while ($row = mysqli_fetch_array($result)) {
		if ($row['gpslat'] > 0 and $row['gpslng'] < 0) {
		$ysi_headers = ""; // default is no YSI headers
		if ($row['ysi_layout_id'] != $currentysilayout) {
			// must construct and output headers
			if ($row['ysi_layout_id'] != '0' && $row['ysi_layout_id'] != '') {
				// ysi layout is present, construct header
				// read ysi layout record
				$lresult = mysqli_query($con,"select * from ysi_layout where ysi_layout_id = ".$row['ysi_layout_id']);
				if (mysqli_errno($con) > 0) die(__LINE__.": ".mysqli_error($con));
				// is there a record?
				if (mysqli_num_rows($lresult) == 1) {
					// fetch it into the $ysilayout array
					$ysilayout = mysqli_fetch_array($lresult);
					array_shift($ysilayout); // god only knows
					array_shift($ysilayout); // pop off ID (which we know)
					array_shift($ysilayout); // pop off date (which we don't care about here)
					// build headers
					// start with comma so we can append to existing headers
					$ysi_headers = "";
					for ($i = 0; $i < count($ysilayout); ++$i) {
						if ($ysilayout[$i] != '') {
							// look up header text
							$query = "select ysi_field_desc from ysi_fields where ysi_field_id = ".$ysilayout[$i];
							// echo "query = '$query'\n";
							$hresult = mysqli_query($con,$query);
							if (mysqli_errno($con) > 0) die(__LINE__.": ".mysqli_error($con));
							$ysihead = mysqli_fetch_array($hresult);
							$ysi_headers .= ",".trim($ysihead[0]);
						}
					}
				}
			}
			echo "DateTime,GPS_Lat,GPS_Long,Depth_m,Temp_C,GPS_FixQuality,GPS_n_Sat,GPS_HDoP,GPS_Altitude,GPS_T_TMG,GPS_M_TMG,GPS_SOG_N,GPS_SOG_K,GPS_MagVar"
				. $ysi_headers."\n"; #,YSI_Time,YSI_Temp,YSI_Cond,YSI_DO,YSI_DOchrg,YSI_Depth,YSI_pH,YSI_Turb,YSI_Chlor,YSI_ChlorRFU,YSI_Battery\n";
			$currentysicount = count($ysilayout);
		}
		$currentysilayout = $row['ysi_layout_id'];
		echo $row['recdate'].",".$row['gpslat'].','.$row['gpslng'].",".
			$row['depthm'].','.$row['tempc'].','.$row['gpsfixquality'].','.
			$row['gpsnsats'].','.$row['gpshdop'].','.$row['gpsalt'].','.
			$row['gpsttmg'].','.$row['gpsmtmg'].','.$row['gpssogn'].','.
			$row['gpssogk'].','.$row['gpsmagvar'];
		if ($currentysilayout != '') {
			for ($i = 0; $i < $currentysicount; ++$i) {
				if ($ysilayout[$i] != '') {
					echo ",\"".$row[sprintf("ysi_%02d", $i+1)]."\"";
				}
			}	
		}
		echo "\n";
		}
	}
	exit;
}

if ($_REQUEST['csvdownload'] == "yes" && $_REQUEST['path'] == '1') {
	// KML path download requested
	$query=("select convert_tz(recdate,'GMT','$TZ') as lrecdate, trackingdata_flex.* from trackingdata_flex where recdate >= '".$starttime_gmt."' and recdate <= '".$endtime_gmt."' and recordid mod $skip = 0 order by recdate");
	if ($_REQUEST['debug']=='y') {
		echo "query='$query'\n";
	}
	$result = mysqli_query($con,$query);
	if (mysqli_errno($con) > 0) {
		echo __LINE__.": ".mysqli_error($con);
		exit;
	}

	header("Content-type: application/vnd.google-earth.kml+xml");
	header("Content-Disposition: attachment; filename=NeeskayTrack".substr($starttime,0,10).".kml");

	echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	echo '<kml xmlns="http://earth.google.com/kml/2.1">'."\n";

	echo "<Document>\n";
	echo "<name>Neeskay Path</name>\n";
	echo <<<_PATH_
        <Style id="sh_ylw-pushpin">
                <IconStyle>
                        <scale>1.2</scale>
                </IconStyle>
                <LineStyle>
                        <color>ffFF00ff</color>
                        <width>5</width>
                </LineStyle>
        </Style>
        <Style id="sn_ylw-pushpin">
                <LineStyle>
                        <color>ff9900ff</color>
                        <width>5</width>
                </LineStyle>
        </Style>
        <StyleMap id="msn_ylw-pushpin">
                <Pair>
                        <key>normal</key>
                        <styleUrl>#sn_ylw-pushpin</styleUrl>
                </Pair>
                <Pair>
                        <key>highlight</key>
                        <styleUrl>#sh_ylw-pushpin</styleUrl>
                </Pair>
        </StyleMap>
_PATH_;

	echo "<Placemark>\n";
	echo "<name>Neeskay Path</name>\n";
	echo "<description>Path of R/V Neeskay from $starttime to $endtime</description>\n";
	echo "<styleUrl>#msn_ylw-pushpin</styleUrl>\n";
	/*
	<LookAt>
		<longitude>-112.0822680013139</longitude>
		<latitude>36.09825589333556</latitude>
		<altitude>0</altitude>
		<range>2889.145007690472</range>
		<tilt>62.04855796276328</tilt>
		<heading>103.8120432044965</heading>
	</LookAt>
	*/
	echo "<LineString>\n";
	echo "	<tessellate>1</tessellate>\n";
	echo "	<coordinates>\n";
/* -112.0814237830345,36.10677870477137,0 -112.0870267752693,36.0905099328766,0 */
	while ($row = mysqli_fetch_array($result)) {
		// idiot check the data- assume ship is in western
		// and northern hemispheres
		if ($row['gpslng'] < 0 && $row['gpslat'] > 0) {
			echo $row['gpslng'].",".$row['gpslat'].",0\n";
		}
	}

	echo "</coordinates>\n";
	echo "</LineString>\n";
	echo "</Placemark>\n";
	echo "</Document>\n";
	echo "</kml>\n";
	exit;
}


if ($_REQUEST['csvdownload'] == "yes" && $_REQUEST['path'] == '2') {
	// KML points download requested

	$query=("select convert_tz(recdate,'GMT','$TZ') as lrecdate, trackingdata_flex.* from trackingdata_flex where recdate >= '".$starttime_gmt."' and recdate <= '".$endtime_gmt."' and recordid mod $skip = 0 order by recdate");
	if ($_REQUEST['debug']=='y') {
		echo "query='$query'\n";
	}
	$result = mysqli_query($con,$query);
	if (mysqli_errno($con) > 0) {
		echo __LINE__.": ".mysqli_error($con);
		exit;
	}
	header("Content-type: application/vnd.google-earth.kml+xml");
	header("Content-Disposition: attachment; filename=NeeskayTrack".substr($starttime,0,10).".kml");

	echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	echo '<kml xmlns="http://earth.google.com/kml/2.1">'."\n";
	echo "<Document>\n";
	echo "<Style id=\"pointicon\">\n";
	echo "<IconStyle><scale>1.2</scale><Icon><href>http://www.glwi.uwm.edu/images/pointicon.png</href></Icon></IconStyle>\n";
	echo "</Style>\n";
	echo "<Style id=\"pointiconmark\">\n";
	echo "<IconStyle><scale>1.2</scale><Icon><href>http://www.glwi.uwm.edu/images/pointiconmark.png</href></Icon></IconStyle>\n";
	echo "</Style>\n";
	/*
	<LookAt>
		<longitude>-112.0822680013139</longitude>
		<latitude>36.09825589333556</latitude>
		<altitude>0</altitude>
		<range>2889.145007690472</range>
		<tilt>62.04855796276328</tilt>
		<heading>103.8120432044965</heading>
	</LookAt>
	*/
/* -112.0814237830345,36.10677870477137,0 -112.0870267752693,36.0905099328766,0 */
	$mi = 0;
	while ($row = mysqli_fetch_array($result)) {
		// idiot check the data- assume ship is in western
		// and northern hemispheres
		if ($row['gpslng'] < 0 && $row['gpslat'] > 0) {
			echo "<Placemark>\n";
			echo "<description>depth=".$row['depthm']."; date=".$row['recdate']."&lt;br&gt;lat=".$row['gpslat']."&lt;br&gt;long=".$row['gpslng'];
			echo "</description>\n";
			$mi += 1;
			if ($mi == 5) {
				echo "<name>".$row['depthm']."</name>\n";
				echo "<styleUrl>#pointiconmark</styleUrl>\n";
				$extr = "<extrude>1</extrude>\n";
				$mi = 0;
			} else {
				echo "<styleUrl>#pointicon</styleUrl>\n";
				$extr = "";
			}
			echo "<Point>$extr<altitudeMode>relativeToGround</altitudeMode><coordinates>";
			echo $row['gpslng'].",".$row['gpslat'].",".(($maxdepthm - $row['depthm'])*10)."</coordinates></Point>\n";
			echo "</Placemark>\n";
		}
	}
	echo "</Document>\n";
	echo "</kml>\n";
	exit;
}

// graph display

$query=("select convert_tz(recdate,'GMT','$TZ') as lrecdate, tempc, depthm from trackingdata_flex where (recdate between '".$starttime_gmt."' and '".$endtime_gmt."') and recordid mod $skip = 0 order by recdate");
if ($_REQUEST['debug']=='y') {
	echo "query='$query'\n";
}
$result = mysqli_query($con,$query);
if (mysqli_errno($con) > 0) {
	echo __LINE__.": ".mysqli_error($con);
	exit;
}


$data = array();
$xdata = array();
$datay2 = array();
$maxdepth=0;
$maxtemp = 0;
$i=0;
$xmin = "-1"; $xmax = "-1";
$lastdepth = "";
while ($row = mysqli_fetch_array($result)) {
	// correct temperature display
	if ($row['tempc'] <= 0.01 or $row['tempc'] >= 100) {
		$row['tempc'] = '-';
	}
	// correct depth display
	
	if (($row['depthm'] <= 1) or ($row['depthm'] >= 500)) {
		$row['depthm'] = '-';
	}
	$data[$i] = $row['tempc'];
	if ($row['depthm'] == "") {
		$row['depthm'] = $lastdepth;
	}
	if ($row['depthm'] == "") {
		$datay2[$i] = "";
	} else {
		$datay2[$i] = - $row['depthm'];
	}
	if ($maxdepth < $row['depthm']) {
		$maxdepth = $row['depthm'];
	}
	if ($maxtemp < $row['tempc']) {
		$maxtemp = $row['tempc'];
	}
	$lastdepth = $row['depthm'];
	$xdata[$i] = strtotime($row['lrecdate']);
	if ($xmin < 0 or $xmin > $xdata[$i]) { $xmin = $xdata[$i]; }
	if ($xmax < 0 or $xmax < $xdata[$i]) { $xmax = $xdata[$i]; }
	$i++;
}

// fix it so that the proper range is always displayed.
// this is done by inserting null points at the extremes of the range, if necessary.
if ($xmax < 0 or $xmax < strtotime($endtime)) {
	$xdata[$i] = strtotime($endtime);   // place at end time
	$data[$i] = "x";         // "x" is special value that means a missing point
	$datay2[$i] = "x";
	$xmax = strtotime($endtime);
}

if ($xmin < 0 or $xmin > strtotime($starttime)) {
	array_unshift($xdata, strtotime($starttime));
	array_unshift($data, "x");
	array_unshift($datay2, "x");
	$xmin = strtotime($starttime);
}

// Create the new graph
$graph = new Graph($width,$height);

// Slightly larger than normal margins at the bottom to have room for
// the x-axis labels
$graph->SetMargin($leftmargin,$rightmargin,$topmargin,$botmargin);

// use date for the x-axis
//if ($_REQUEST['scale'] == "fixed") {
//	$graph->SetScale('linlin',$_REQUEST['scalebot'],$_REQUEST['scaletop'],$xmin, $xmax);
//} else {
	$graph->SetScale('linlin',0,round($maxtemp)+1,$xmin,$xmax);
//}

/*
if ($maxwind < 20) {
	$maxwind = 20;
} else {
	//nearest 10 higher than maxwin
	$maxwind = ceil($maxwind / 10) * 10;
}
*/
if ($_REQUEST['depthscale'] > 0) {
	$graph->SetY2Scale("lin", -$_REQUEST['depthscale'],0);
} else {
	$graph->SetY2Scale("lin",- (round($maxdepth)+1),0);
}


$graph->yaxis->title->Set("degrees Celcius");
$graph->yaxis->SetTitlemargin(40);

$graph->title->Set($graphtitle);
$graph->title->SetFont(FF_ARIAL, FS_BOLD, 14);

// Set the angle for the labels to 90 degrees
//$graph->xaxis->SetTextTickInterval(60*60,38*60);
//$graph->xaxis->SetTextLabelInterval(2);

//$graph->xaxis->scale->SetDateFormat($dateformat);
$graph->xaxis->SetLabelAngle(60);
$graph->xaxis->SetLabelFormatCallback('DateTimeCallback');


$tickspacing = 0;

$graphrange = $xmax - $xmin;
$tickspacingmin = $graphrange * 20 / ($width-($leftmargin+$rightmargin));
// round up tickspacingmin to the next sensible value
foreach (array(1,5,10,15,20,30,60,   // seconds
		60*5, 60*10, 60*15, 60*20, 60*30, 60*60, // minutes
		3600*2, 3600*3, 3600*4, 3600*6, 3600*8,
			3600*12, 3600*24,  // hours
		3600*24*2, 3600*24*7, 3600*24*14, 3600*24*28,
		3600*24*60, 3600*24*90, 3600*24*120,
			3600*24*180, 3600*24*365) // days
		as $interval) {
	if ($tickspacingmin < $interval) {
		$tickspacing = $interval;
		break;
	}
}

$ticklist = array();
for($t=$xmin; $t <= $xmax; $t += $tickspacing) {
        $t = localreround($t, $tickspacing);
        $ticklist[] = $t;
}

$graph->xaxis->SetMajTickPositions($ticklist);




//$firsttickoffset = (ceil($xmin / $tickspacing)*$tickspacing) - $xmin;

// set tick spacing
//$graph->xaxis->scale->ticks->set($tickspacing);


// set starting tick offset
//$graph->xaxis->SetTextTickInterval(0,$firsttickoffset);


//$graph->xaxis->HideTicks(true,false);

//$graph->xaxis->scale->SetTimeAlign( MINADJ_1, MIDADJ_1);

$graph->xaxis->SetFont(FF_VERDANA, FS_NORMAL, 7);
//$graph->yaxis->SetFont(FF_VERDANA, FS_NORMAL, 10);

$line = new LinePlot($data,$xdata);
// $line->SetLegend('Year 2005');
//$line->SetFillColor('gray@0.4');
$line->SetWeight(2.0);
$line2 = new LinePlot($datay2,$xdata);
$line2->SetColor("#8b4513@0.88");
$line2->SetFillColor("cyan@0.92");
$line2->SetWeight(2);



$graph->y2axis->SetLabelFormatCallback("DepthCallback");
$graph->y2axis->SetColor("brown");
$graph->y2axis->title->Set("depth (m)");
$graph->y2axis->title->SetColor("brown");
$graph->y2axis->SetTitlemargin(25);
$graph->y2axis->SetTitleSide(SIDE_RIGHT);
$graph->y2axis->SetPos('max');

$graph->Add($line);

$graph->AddY2($line2);

if ($_REQUEST['debug'] != 'y') {
	$graph->Stroke();
}
?>
