<?
// Module to streamline the processing of Google Maps plots.
//
// Basic idea: store the output of trackingimg-nmea.php in a file, and 
//   then pass the URL of that file to Google Maps.  That way,
//   Google Maps isn't waiting for the data.
//

	// clear out old kml files
	system("/opt/neeskay/bin/housekeep.sh");

	// establish file name
	$tempkml = "gmaps/gm" . time() . ".kml";

	// get the output
	$output = shell_exec("wget -O ".dirname(__FILE__)."/$tempkml '" . $_REQUEST['query'] . "'");

	header("Status: 302 Moved");
	header("Location: http://maps.google.com/?q=http://".$_SERVER['HTTP_HOST']."/neeskay/".$tempkml);
?>
<html></html>
