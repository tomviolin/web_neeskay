<html>
<meta http-equiv="Refresh" content="5">
<head>
<style type=text/css>
h1 {margin-top:0px; margin-bottom:0px;}
h2 {margin-top:0px; margin-bottom:0px;}
td {text-align: center;}
</style>
<body>
<?
	$cudahy = `tail -30 /opt/neeskay/data/xtend.csv | grep ",1,-" | tail -1`;
	$cudarray = split(",", $cudahy);
	$enderis = `tail -30 /opt/neeskay/data/xtend.csv | grep ",2,-" | tail -1`;
	$endarray = split(",", $enderis);
?>
<table border=0 align=center valign=center>
<tr><td colspan=3><h1>Range Testing Display</h1></td></tr>
<tr><td height=12></td></tr>
<tr>
	<td nowrap><h2>Cudahy Tower</h2></td>
	<td width=30></td>
	<td nowrap><h2>Enderis Hall</h2></td>
</tr>
<tr>
	<td ><img src="CudahyTower-001.jpg"></td>
	<td width=30></td>
	<td ><img src="enderis-digital.jpg"></td>
</tr>
<tr>
 	
	<td><h1><font color="#FF3333"><?=$cudarray[15];?></font></h1><?=$cudarray[0]?></td>
	<td></td>
	<td><h1><font color="#FF3333"><?=$endarray[15];?></font></h1><?=$endarray[0]?></td>
</tr>
</table>

</body>
</html>
