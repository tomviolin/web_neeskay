<?php
	$x = json_decode(file_get_contents("bathydatapoints.json"));
	if ($_GET['action']=="next" || $_GET['action']=="prev") {
		for ($i = 0; $i < count($x->points)-1; ++$i) {
			if ($x->points[$i]->recdate == $_GET['recdate']) {
				if ($_GET['action']=="next") {
					$idx = $i+1;
				} else {
					$idx = $i-1;
				}
				echo json_encode($x->points[$idx])."\n";
				exit(0);
			}
		}
	}


	list($plat,$plng) = explode(',',$_GET['point']);
	$mindist = 9999999;
	$mindisti = -1;
	foreach ($x->points as $key=>$val) {
		if ($val->recdate != "") {
			$dist =   pow($val->lat - $plat, 2)
				+ pow($val->lng - $plng, 2);
			if ($dist < $mindist) {
				$mindist = $dist;
				$mindisti = $key;
			}
		}
	}
	if ($mindisti == -1) {
		echo json_encode("");
	} else {
		echo json_encode($x->points[$mindisti])."\n";
	}
?>
