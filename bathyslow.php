<?
if ($_REQUEST['latmin'] != "") {
	// form submission
	if ($_REQUEST['rel'] != '') {
		$bboxstr = "Rel";
	} else {
		$bboxstr = "Abs";
	}
	$bboxstr .= ",".$_REQUEST['latmin']
			.",".$_REQUEST['latmax']
			.",".$_REQUEST['lngmin']
			.",".$_REQUEST['lngmax'];
	file_put_contents("/var/www/neeskay/bbox.csv",$bboxstr);
	header("Status: 302 moved");
	header("Location: /neeskay/bathy.php");
	echo "<html></html>\n";
	exit(0);
}
?><html>
<head>
<?
	$bbox = explode(",",trim(file_get_contents("/var/www/neeskay/bbox.csv")));
?>
<script type="text/javascript">
	function update() {
		document.bath.src = "bathy.jpg?rand=" + Math.random();
		setTimeout("update()", 4000);
	}
</script>
</head>
<body onload="update()">
<form name="adj" style="margin:0">
<a href="" onclick="alert(window.status)" ><img name="bath" src="bathy.jpg" ismap border=0></a>

<table border=0 style="background-color: #CCFFFF">
<tr>
	<td valign=middle><b>Bounding box:</b></td>
	<td valign=middle><input type="checkbox" name="rel" <?=($bbox[0]=="Rel")?'checked':''?>></td>
	<td valign=middle>Relative</td>
	<td valign=middle>Lat Min:</td><td><input type="text" name="latmin" value="<?=$bbox[1]?>" size=4></td>
	<td valign=middle>Lat Max:</td><td><input type="text" name="latmax" value="<?=$bbox[2]?>" size=4></td>
	<td valign=middle>Lng Min:</td><td><input type="text" name="lngmin" value="<?=$bbox[3]?>" size=4></td>
	<td valign=middle>Lng Max:</td><td><input type="text" name="lngmax" value="<?=$bbox[4]?>" size=4></td>
	<td valign=middle><input type="submit" value="Apply"></td>
</tr>
</table>
</form>
</body>
</html>
