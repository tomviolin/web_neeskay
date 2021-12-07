<?php

mysql_connect("waterdata.glwi.uwm.edu","shipuser","arrrrr");
mysql_select_db ("neeskay");

$startdate = "2005-01-01";
$enddate = "2013-12-31";
$intervalid = 2;
//echo $_GET['startdate']."-".$_GET['enddate']."\n";
if (isset($_GET['startdate'])) {
	$startnum = doubleval($_GET['startdate'])/1000-60*60*0;
	$startdate = strftime("%F %T",$startnum);
	$intervalid = 1;
}
if (isset($_GET['enddate'])) {
	$endnum = doubleval($_GET['enddate'])/1000+60*60*0;
	$enddate = strftime("%F %T",$endnum);
	$intervalid = 1;
}


file_put_contents("blah.txt",$startdate."-".$enddate);

//echo $startnum."-".$endnum."\n";

// July 01 2012 12:22:23,20,10
// July 01 2012 12:23:23,25,10
// July 01 2012 12:24:23,21,11

// granularity of data

$interval = strtotime($enddate)-strtotime($startdate);

if ($intervalid == 1 && $interval < 86400) {
	// less than 48 hours- all data
	$table = "trackingfast";
	$recdateexpr = 'recdate';
	$depthmexpr = 'depthm';
	$tempcexpr = "tempc";
	$groupby = "";
} else {
	// once an hour
	$table = "trackingfast_hourly";
	$recdateexpr = "rechour";
	$depthmexpr = "maxdepthm";
	$tempcexpr = "avgtempc";
	$groupby = "";
}

$query2 = ("select max($depthmexpr) from $table where $recdateexpr >= CONVERT_TZ('$startdate','SYSTEM','GMT') and $recdateexpr < CONVERT_TZ('$enddate','SYSTEM','GMT') and $depthmexpr < 300");
$result2 = mysql_query($query2);
echo $_GET['callback']."(\n".'{"status":"';
if (mysql_errno()) echo mysql_error().": ".$query2;
echo '","maxdepth": ';
$rowx = mysql_fetch_array($result2);
echo $rowx[0].",\n\"data\": [\n";

$query = ("select $recdateexpr as recdatefld, $depthmexpr as depth, $tempcexpr as temp from $table where $recdateexpr >= convert_tz('$startdate','system','gmt') and $recdateexpr < convert_tz('$enddate','system','gmt') and $depthmexpr < 300 $groupby");
//echo $query."\n";
$result = mysql_query($query);
if (mysql_errno()) echo mysql_error()." ".$query."\n";
$comma="";
while ($row = mysql_fetch_array($result)) {
	echo "{$comma}[".(strtotime($row['recdatefld'])*1000).",".$row['depth'].','.$row['temp']."]";
	$comma=",\n";
}
echo "]});";
?>
