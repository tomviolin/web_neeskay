<?php

$m=json_decode(file_get_contents("bathyimageinfo.json"));
$m->log = `tail -50 /home/tomh/bathydaemon.log | egrep -v "^%" | tail -5`;
echo json_encode($m);
?>
