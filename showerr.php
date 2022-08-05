<?php
	// pass back any error message from the data monitoring system
$ERRORFLAG = "/opt/neeskay/bin/error.flag";
$SHIPCURRENT = "/opt/neeskay/data/shipdata-current.csv";

if (file_exists($ERRORFLAG) &&
    filesize($ERRORFLAG)>0) {
	$msg = file_get_contents($ERRORFLAG);
	header("Content-Type: text/plain");
	echo "E";
	readfile($ERRORFLAG);
} elseif (file_exists($SHIPCURRENT)) {
	header("Content-Type: text/plain");
	$sdata = explode(",",file_get_contents($SHIPCURRENT));
	$age = time()-filemtime($SHIPCURRENT);
	$blink = $age == floor($age/2)*2;
	echo "<!-- $age -->\n";
	if ($age < 3) {
		$color = 'black';
	} elseif ($age < 10) {
		if ($blink) {
			$color = "yellow";
		} else {
			$color = "red";
		}
	} else {
		echo "<span style='color:red; font-size:30px; color:".($blink?"red":"white")."'>NO DATA RECORDING for $age sec.</span>";
		exit(0);
	}
	$sec = $sdata[0][18]-0;
	if (floor($sec/2)*2 == $sec) {
		$bcol = "black";
	}else {
		$bcol = "green";
	}
	echo "<span style='color:$color; font-size:20px;'>".$sdata[0]." UTC</span>&nbsp;&nbsp;<span style='color: $bcol; font-size: 25px'>●</span>";
}
exit(0);
?>
