<?php

$tileserverurl = 'http://tile.openstreetmap.org';
$tileformat='png';

header("Content-type:","image/png");

$p=$_SERVER['PATH_INFO'];

if (file_exists("tc$p")) {
	readfile("tc$p");
	exit(0);
}


$tileurl = "$tileserverurl$p";
mkdir(dirname("tc$p"),0777,true);
copy($tileurl, "tc$p");
readfile("tc$p");

?>
