<?php
header("Content-type: image/png");



// define map dimensions
$twidth=894;
$theight=638;

$waterr=181;
//$waterg=214; // 208
//$waterb=241; // 208
$waterg=208;
$waterb=208;

//$tileserverurl = 'http://otile3.mqcdn.com/tiles/1.0.0/osm/$zoom/$tilex/$tiley.png';
//$tileformat = 'png';

//$tileserverurl = 'http://tile.openstreetmap.org/$zoom/$tilex/$tiley.png';
//$tileformat='png';

$tileserverurl = 'http://localhost/neeskay/tilecache/$zoom/$tilex/$tiley.png';
$tileformat='png';

//$tileserverurl = 'http://mt0.google.com/vt/lyrs=t@127,r@163000000&hl=en&src=api&x=$tilex&y=$tiley&z=$zoom';
//$tileformat="jpg";


// echo "starting up...\n";
function llz2xy($lat,$lon,$zoom) {
	$xtile = floor((($lon + 180) / 360) * pow(2, $zoom));
	$ytile = floor((1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / pi()) /2 * pow(2, $zoom));
	return array($xtile,$ytile);
}

function llz2xyfloat($lat,$lon,$zoom) {
	$xtile = ((($lon + 180) / 360) * pow(2, $zoom));
	$ytile = ((1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / pi()) /2 * pow(2, $zoom));
	return array($xtile,$ytile);
}

function xyz2ll($xtile, $ytile, $zoom) {
	$n = pow(2, $zoom);
	$lon_deg = $xtile / $n * 360.0 - 180.0;
	$lat_deg = rad2deg(atan(sinh(pi() * (1 - 2 * $ytile / $n))));
	return array($lat_deg,$lon_deg);
}

function log2($x) {
	return log($x)/log(2);
}

function maketransparent($im) {
	imagetruecolortopalette($im,false,127);
	// make sure the desired water color exists
	// echo imagesx($im).",".imagesy($im).":";
	$watercolor = imagecolorexact($im, 0,0,0); // $waterr,$waterg,$waterb);
	if ($watercolor == -1) {
		$watercolor = imagecolorallocate($im, 0,0,0);// $waterr, $waterg, $waterb);
	}
	echo $watercolor.",";
	// read the sample of water color
	$si = imagecreatefrompng("mqwater.png");
	imagetruecolortopalette($si, false, 127);
	// loop through all the colors
	for ($x=0; $x < imagesx($si); ++$x) {
		for ($y = 0; $y < imagesy($si); ++$y) {
			$colorat = imagecolorat($si,$x,$y);
			echo "[$colorat],";
			$colors = imagecolorsforindex($si,$colorat);
			$r=$colors['red'];
			$g=$colors['green'];
			$b=$colors['blue'];
			//echo "($r,$g,$b),";
			$colormatch = imagecolorresolve($im,$r,$g,$b);
			if ($colormatch > -1){
				echo $colormatch.",";
				imagecolorset($im, $colormatch, 0,0,0); //$waterr,$waterg,$waterb);
			}
		}
	}
	echo "\n";
	imagedestroy($si);
}


$bbx1=$_REQUEST['bbx1'];
$bbx2=$_REQUEST['bbx2'];
$bby1=$_REQUEST['bby1'];
$bby2=$_REQUEST['bby2'];


//echo "bbox=[$bbx1,$bby1, $bbx2,$bby2]\n";

// calculate degrees per pixel
$degpp = ($bbx2-$bbx1)/$twidth;

// calculate zoom level needed
$zoom = ceil(log2(360/(256*$degpp)));

// echo "zoom=$zoom\n";

// determine map tile coordinates for bounding box
list($bbtilex1, $bbtiley1) = llz2xy($bby1, $bbx1, $zoom);
list($bbtilex2, $bbtiley2) = llz2xy($bby2, $bbx2, $zoom);

//echo "map xy coords=[$bbtilex1,$bbtiley1], [$bbtilex2, $bbtiley2]\n";

// assemble map
$mapwidth = ($bbtilex2-$bbtilex1+1)*256;
$mapheight= ($bbtiley1-$bbtiley2+1)*256;

$mapim = imagecreatetruecolor($mapwidth, $mapheight);
imagealphablending($mapim, true);
$transp = imagecolorallocatealpha($mapim, 181,208,208, 127);
imagefill($mapim, 0,0, $transp);
for ($tilex = $bbtilex1; $tilex <= $bbtilex2; ++$tilex) {
	for ($tiley = $bbtiley2; $tiley <= $bbtiley1; ++$tiley) {
		$tileurl = eval('return "'.$tileserverurl.'";');
		switch ($tileformat) {
			case 'png': $tile = imagecreatefrompng($tileurl); break;
			case 'jpg': $tile = imagecreatefromjpeg($tileurl); break;
		}
		//imagealphablending($tile, TRUE);
		//$t = imagecreatetruecolor(256,256);
		//imagealphablending($t);
		//imagecopy($t, $tile, 0,0,0,0,256,256);
		//imagedestroy($tile);
		//$tile=$t;
		//$t=NULL;
		//maketransparent($tile);
		$watercolor = imagecolorresolve($tile, $waterr,$waterg,$waterb);
		imagecolortransparent($tile, $watercolor);

		imagecopy($mapim, $tile, ($tilex-$bbtilex1)*256, ($tiley-$bbtiley2)*256, 0,0,256,256);
		imagedestroy($tile);
	}
}

$watercolor = imagecolorresolve($mapim, $waterr,$waterg,$waterb);
imagecolortransparent($mapim, $watercolor);

// now calculate cropping

// determine map tile fractional coordinates for bounding box
list($bbtilex1float, $bbtiley1float) = llz2xyfloat($bby1, $bbx1, $zoom);
list($bbtilex2float, $bbtiley2float) = llz2xyfloat($bby2, $bbx2, $zoom);

// calculate cropping bounds
$cropx1 = floor(($bbtilex1float - $bbtilex1)*256);
$cropx2 = floor(($bbtilex2float - $bbtilex1)*256);

$cropy1 = floor(($bbtiley2float - $bbtiley2)*256);
$cropy2 = floor(($bbtiley1float - $bbtiley2)*256);

$ntwidth = $cropx2-$cropx1;
$ntheight= $cropy2-$cropy1;
//echo "mapwidth=$mapwidth, mapheight=$mapheight\n";
//echo "bbtilex1float=$bbtilex1float, bbtilex2float=$bbtilex2float\n";
//echo "bbtilex1=$bbtilex1, bbtilex2=$bbtilex2\n";
//echo "bbtiley1float=$bbtiley1float, bbtiley2float=$bbtiley2float\n";
//echo "bbtiley1=$bbtiley1, bbtiley2=$bbtiley2\n";


//echo "cropx1=$cropx1, cropx2=$cropx2, cropy1=$cropy1, cropy2=$cropy2, ntwidth=$ntwidth, ntheight=$ntheight<br>\n";
$tmap = imagecreatetruecolor($ntwidth, $ntheight);
imagealphablending($tmap, true);
$transp = imagecolorallocatealpha($tmap, $waterr,$waterg,$waterb, 127);
imagefill($tmap, 0,0, $transp);
imagecopymerge($tmap, $mapim, 0,0,$cropx1, $cropy1, $ntwidth, $ntheight, 100); //$cropx2-$cropx1, $cropy2-$cropy1);

$watercolor = imagecolorresolve($tmap, $waterr,$waterg,$waterb);
imagecolortransparent($tmap, $watercolor);

imagepng($tmap);
imagedestroy($mapim);
imagedestroy($tmap);

?>
