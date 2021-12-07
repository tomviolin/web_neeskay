<?

function localreround($xmin, $mod) {

	//$mod = 3600*3;
	//$xminstr = $argv[1];
	//$xmin = strtotime($xminstr." GMT");

	if ($mod <= 3600) {
		$xminround = floor($xmin/$mod)*$mod;
		if ($xminround < $xmin) $xminround += $mod;
		return $xminround;
	}

	$xminlclstr = date("M d Y H:i:s", $xmin);
	$fakelocaldatenum = strtotime($xminlclstr." GMT");

	$roundedfake = floor($fakelocaldatenum/$mod)*$mod;
	if ($roundedfake < $fakelocaldatenum) {
		$roundedfake += $mod;
	}

	// ok now we have a new possible date. convert it back to "GMT" 

	$roundedgmt = strtotime(gmdate("M d Y H:i:s", $roundedfake));

	if ($mod <= 3600) return $roundedgmt;


	$roundedrelocal = date("M d Y H:i:s", $roundedgmt);

	$roundedrefake = strtotime($roundedrelocal." GMT");
	$reroundedrefake = floor($roundedrefake/$mod)*$mod;
	if ($reroundedrefake < $roundedrefake) {
		$reroundedrefake += $mod;
	}

	$reroundedgmt = strtotime(gmdate("M d Y H:i:s", $reroundedrefake));

	return $reroundedgmt;
}

?>
