<?
session_start();

function cvtdeg($indeg) {
	if (strpos($indeg,",") === false) {
		return ($indeg);
	}
	$degs = preg_split("/,/", $indeg);
	$retval = $degs[1]+($degs[2]/60)+($degs[3]/3600);
	if ($degs[0]=="S" || $degs[0] == "W") {
		$retval = -$retval;
	}
	return $retval;
}

$action = "";

if (isset($_REQUEST['formaction'])) {
	// form sumission
	$action = $_REQUEST['formaction'];
}


if ($action == "getsettings") {
	// get saved profile
	if(file_exists('bbox'.($_REQUEST['savedfile']+0).'.csv')) {
		copy('bbox'.($_REQUEST['savedfile']+0).'.csv','bbox.csv');
	}
	header("Status: 302 moved");
	header("Location: /neeskay/bathy.php?dialog=yes");
	echo "<html></html>\n";
	exit(0);
	
} elseif ($action == "delete") {
	// delete saved profile
	@unlink('bbox'.($_REQUEST['savedfile']+0).'.csv');
	header("Status: 302 moved");
	header("Location: /neeskay/bathy.php");
	echo "<html></html>\n";
	exit(0);

} else {
	if ($action != "") {
		// form submission
		if ($_REQUEST['rel'] != '') {
			$bboxstr = "Rel";
		} else {
			$bboxstr = "Abs";
		}
		$bboxstr .= ",".cvtdeg($_REQUEST['latmin'])
				.",".cvtdeg($_REQUEST['latmax'])
				.",".cvtdeg($_REQUEST['lngmin'])
				.",".cvtdeg($_REQUEST['lngmax'])
				.",".$_REQUEST['desc']
				.",".$_REQUEST['paramcol']
				.",".stripslashes($_REQUEST['start'])
				.",".stripslashes($_REQUEST['end'])
				.",".stripslashes($_REQUEST['cex'])
				.",".stripslashes($_REQUEST['lag'])
				.",".html_entity_decode(stripslashes($_REQUEST['title']),ENT_NOQUOTES)
				.",".stripslashes($_REQUEST['zmin'])
				.",".stripslashes($_REQUEST['zmax'])
				.",".stripslashes($_REQUEST['mapping'])
				."\n";
		file_put_contents("/var/www/neeskay/bbox.csv",$bboxstr);
		if ($action == "savenamed") {
			if ($_REQUEST['desc'] == '') {
				$bboxstr[4] = "saved ".date("H:i:s");
			}
			file_put_contents("/var/www/neeskay/bbox".time().".csv",$bboxstr);
			header("Status: 302 moved");
			header("Location: /neeskay/bathy.php?dialog=yes");
		}
		echo "<html>
<script>
</script>
</html>
";
		exit(0);
	}
}
?><html>
<head>
<?
	$bbox = explode(",",trim(file_get_contents("/var/www/neeskay/bbox.csv")));
?>
<link rel="stylesheet" href="jquery/css/ui-lightness/jquery-ui-1.8.16.custom.css">
<script type="text/javascript" src="jquery/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="jquery/js/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript" src="clickutil.js"></script>
<script type="text/javascript">
	var formaction="<?=$_GET['formaction']?>";
	var initDialog=<?=$_GET['dialog']=="yes"?"true":"false"?>;
	var bathLoaded = true;
	var timercount = 0;
	var imageData = {};
	var newImageData;
	function doBathLoaded() {
		bathLoaded=true;
		//console.log("bath loaded:"+bathyimage.src);
		$.get("getimageinfo.php?<?=SID?>&rnd="+Math.random(), function(data) {
			newImageData = $.parseJSON(data);
			//console.log(data);
			//console.log(newImageData);
			if (imageData.time && newImageData.time != imageData.time) {
				setTimeout("closeDialog()",2000);
			} else {
				//$('#progressdialog').dialog('option','title','hang on...'+newImageData.coords.time);
			}
			imageData = newImageData;
		});
		//console.log('get launched');
	}
	var lastLoadImageData=0;
	function update() {
		var bathyimage = document.getElementById("bath");
		var newImageData;
		console.log('update');
		var delay=2000;
		if ($('#progressdialog').dialog('isOpen')) {
			delay=200;
		}

		//bathyimage.onclick=function(e) {
		//	doMapClick(e);
		//};
		if (bathLoaded) {
			$.get("getimageinfo.php?<?=SID?>&rnd="+Math.random(), function(data) {
				newImageData = $.parseJSON(data);
				$('#progresslog').html(newImageData.log);
				console.log(newImageData.log);
				if (lastLoadImageData == 0 || lastLoadImageData.time != newImageData.time) {
					bathLoaded = false;
					bathyimage.src = "bathy.jpg?rand=" + Math.random();
					lastLoadImageData = newImageData;
				}
				
				setTimeout("update()", delay);
			});
		} else {
			console.log("bath update posponed.");
			setTimeout("update()", delay);
		}
	}

	function openDialog(title) {
		$('#mapclick').dialog("close");
		$('#tabs').tabs("select",0);
		$(document).scrollTop(0);
		$('progresslog').innerHTML = '';
		$('#progressdialog').dialog('options',{title:title});
		$('#progressdialog').dialog('open');
	}

	function closeDialog() {
		$('#progressdialog').dialog('close');
	}

	// information about map click
	var clickPos;
	var dx;
	var dy;
	var ux;
	var uy;
	var orgmpx, orgmpy;
	var nux1,nux2,nuy1,nuy2;
	var mevent;

	// map click handler routine
	function doMapClick(e) {
		$('#mapclick').dialog('close');
		$('#pointinfo').dialog('close');
		clickPos = GraphSys.GetCoordinates(e,document.getElementById("bath"));
		dx = clickPos.x;
		dy = clickPos.y;
		orgmpx=e.pageX-6;
		orgmpy=e.pageY-6;
		$('#mappoint').css('top',(orgmpy)+'px');
		$('#mappoint').css('left',(orgmpx)+'px');
		$('#mappoint').css('display','block');


		with (imageData) {
			ux = (ux2-ux1)*(dx-dx1)/(dx2-dx1)+ux1;
			uy = (uy2-uy1)*(dy-dy1)/(dy2-dy1)+uy1;
		}
		$('#mapclick').dialog("option","title", "Point: "+Math.round(ux*100000)/100000+","+Math.round(uy*100000)/100000);
		mevent = e;
		$('#mapclick').dialog("option", { position: [e.clientX+5,e.clientY+5] });
		with (imageData) {
			if (dy < dy2 && dx < dx1) {
				panb = "Pan NW";
			} else if (dy < dy2 && dx > dx2) {
				panb = "Pan NE";
			} else if (dx > dx2 && dy > dy1) {
				panb = "Pan SE";
			} else if (dy > dy1 && dx < dx1) {
				panb = "Pan SW";
			} else if (dx < dx1) {
				panb = "Pan W";
			} else if (dx > dx2) {
				panb = "Pan E";
			} else if (dy < dy2) {
				panb = "Pan N";
			} else if (dy > dy1) {
				panb = "Pan S";
			}
		}
		$('#mapclick').dialog("option","buttons", [
			{text: "Zoom In", click: function() { zoomInOut("zoomin"); }},
			{text: "Zoom Out", click:function() { zoomInOut("zoomout"); }},
			{text: "Recenter", click:function() { zoomInOut("recenter"); }},
			{text: "MegaZoom", click:function() { zoomInOut("megazoom"); }},
//			{text: panb, click:function() { zoomInOut(panb); }},
			{text: "Identify Nearest Point", click: function() { identifyPoint('idpoint'); }}
		]);
		$('#mapclick').dialog("open");
		// mapclick dialog has clickable choices
		// that will dispach one of the following functions
	}

	function zoomInOut(operation) {
		// zoomIn = true: zoom in
		// zoomIn = false: zoom out
		$('#mapclick').dialog('close');
		with (imageData) {
			factor=2;
			if (operation=="zoomin") {
				factor=0.25;
			} else if (operation=="zoomout") {
				factor=1;
			} else if (operation=="megazoom") {
				factor=0.0625;
			} else if (operation=="recenter") {
				factor=0.5;
			}

			nux1 = ux - (ux2-ux1)*factor;
			nux2 = ux + (ux2-ux1)*factor;
			nuy1 = uy - (uy2-uy1)*factor;
			nuy2 = uy + (uy2-uy1)*factor;
			frm = document.getElementById("adjform"); 
			with (frm.elements) {
				latmin.value=nuy1;
				latmax.value = nuy2;
				lngmin.value = nux1;
				lngmax.value = nux2;
			}
			frm.formaction.value="saveonly";
			openDialog("Redrawing...");
			frm.submit();
		}
	}

	var pointInfo;
	function identifyPoint(action,param) {
		if (action == 'next' || action == 'prev') {
		} else {
			if ($('#pointinfo').dialog('isOpen')) {
				$('#pointinfo').dialog('close');
			}
		}
		if (action == "next" || action == "prev") {
			params = "action="+action+"&recdate="+escape(param);
		} else {
			params = "point="+uy+","+ux;
		}
		$.get("getdatapoint.php?<?=SID?>&"+params+"&rnd="+Math.random(), function(data) {
			point = $.parseJSON(data);
			if (!!!point.recdate) {
				alert("Points cannot be identified at this zoom level.\nTry zooming in.");
				return false;
			}
			// save coordinates of point marker
			var mcpos = $('#mapclick').dialog('option','position');
			// close map click dialog
			$('#mapclick').dialog('close');
			// populate point info dialog
			var pi=document.getElementById("pointinfo");
			pi.innerHTML = "coords="+point.lat+","+point.lng+"<br>"
				+"depth="+point.depth+"<br>"
				+"date="+point.recdate+"<br><br>"
				/*
				+"<a href=\"" + 
				"http://neeskay.dyndns.org/phpmyadmin/import.php?db=neeskay&table=bathyfast&show_query=1&sql_query="
				+escape("select * from bathyfast where recdate >= '"+imageData.points[ci].date)
				+"'\">phpMyAdmin</a><br>" */
				+"<a href=\"/neeskay/?startdate="+escape(point.recdate)+"\" target=\"_blank\">Show in Ship Data Tracking System</a> "
				+"<a accesskey='p' href=\"javascript:void(identifyPoint('prev',point.recdate))\">&lt;<u>p</u>rev</a>&nbsp;&nbsp;"
				+"<a accesskey='n' href=\"javascript:void(identifyPoint('next',point.recdate))\"><u>n</u>ext&gt;</a>";

			// compute device coordinates of point
			var nx, ny; // nearest point device coordinates
			var px=point.lng; // user coordinates of nearest point
			var py=point.lat;
			with (imageData) {
				nx = (dx2-dx1)*(px-ux1)/(ux2-ux1)+dx1;
				ny = (dy2-dy1)*(py-uy1)/(uy2-uy1)+dy1;
			}
			// now use the previously known position of the
			// point box as calculated by the mouse click routine
			// to calculate where to place it so that it is over the nearest point
			//var mpoffsetX = orgmpx - dx;
			//var mpoffsetY = orgmpy - dy;
			var ppos = GraphSys.convertImageToPageXY(document.getElementById("bath"), nx, ny);
			$('#mappoint').css('display','block');
			
			$('#mappoint').animate({
				//'top': (ny + mpoffsetY),
				//'left': (nx + mpoffsetX)},
				'top': ppos.pageY-6,
				'left': ppos.pageX-6},
				100, 
				function() {
					if ($('#pointinfo').dialog('isOpen')) {
					} else {
						$('#pointinfo').dialog('open');
					}
					$('#pointinfo').dialog('option','position',
						//[nx+mpoffsetX-195,ny+mpoffsetY+15]
						[ppos.clientX-195,ppos.clientY+15]
					);
				}
			);
		});
	}

</script>
<title>Dynamic Spatial Data Display</title>
<style type="text/css">
fieldset {display:inline;}
input.middle {text-align: center; }
input[type=text] {
	padding-left: 3px; padding-right: 3px;
}
body {
	font-size: 0.82em;
}
.ui-dialog {
	xxfont-size: 0.95em;
}
#mapclick.ui-dialog-content {
	height: 0px;
	min-height: 0px;
	max-height: 0px;
	display:none !important;
}
.ui-widget-header { color:black; }
</style>
</head>
<body>

<script>
$(document).ready(function(){
	$('#tabs').tabs({select: function(e,ui) {
		//$('#mapclick').dialog('close');
		$('.ui-dialog-content').dialog('close');
	}});
	$('#progressdialog').dialog({ show: 'fade', hide: 'fade',autoOpen: false, modal: true, width: 400, height:250 });
	$('#mapclick').dialog({ modal:false, autoOpen: false, show: 'fade', height:150, width: 325,beforeClose: function(e,ui) {
		$('#mappoint').css('display','none');
	}});
	$('#pointinfo').dialog({ width: 400, show: 'fade', hide: 'fade', autoOpen: false, beforeClose: function(e,ui){
		$('#mappoint').css('display','none');
		return(true);
	}});
	$('#mappoint').css('position','absolute');
	$('#mappoint').css('display','none');
	if (initDialog) {
		openDialog();
	}
		$('#bath').click(function(e) {
			//console.log('map clicked');
			//console.log(e);
			doMapClick(e);
			return true;
		});
	update();
});
</script>
<form name="adj" id="adjform" style="margin:0" target="submittarget">


<div id="tabs">
	<ul>
		<li><a href="#tabs-1">Map Display</a></li>
		<li><a href="#tabs-2">Settings</a></li>
		<li>


	<select name="savedfile"><option value="">--- Select previously saved query settings ---</option>
<?
	$x = glob("bbox1*.csv");
	foreach ($x as $fname) {
		$c=file_get_contents($fname);
		$ca = split(",", $c);
		$cadate = date("Y-m-d",substr($fname,4,10));
		echo "<option value=\"".(substr($fname,4,10)+0)."\">$cadate:{$ca[5]}</option>\n";
	}
?>
	</select>
	<input type="button" name="get" value="Get Settings" onclick="form.formaction.value='getsettings'; form.target=''; form.submit()">
	<input type="button" style="color:red" name="delete" value="DELETE settings" onclick="if (confirm('Delete saved view?\n\n[OK]      = Yes, DELETE\n\n[Cancel] = No, DO NOT DELETE')) { form.target=''; form.formaction.value='delete'; form.submit(); } else { return false;}">
	<input type="button" name="saveonly2" style="color:blue" value="Reapply Settings"
	onclick="form.formaction.value='saveonly'; openDialog('update from form...'); form.submit();">
	<input type="button" value="Refresh" onclick="document.location.href='bathy.php?rand=<?=time()?>';return false;">
<br>

	</li>


	</ul>

	<div id="tabs-1">
	<center>
<div>
<div><img height=640 width=960 name="bath" id="bath" ismap border=0 onload="doBathLoaded()"></div>
</div>
<br>
<a href="bathy.kmz?rand=<?=time()?>" title="overlay this on Google Earth!!">Download Currently Displayed Map/Data as KMZ File</a>
</center>
</div>
<div id="tabs-2">
<fieldset style="background: #ccffff;">
<fieldset><legend>Query Settings
</legend>
	Query Name:<input type="text" size=30 name="desc" value="<?=$bbox[5]?>"><input type="button" name="save" value="Save to this Query Name" onclick="form.target=''; form.formaction.value='savenamed'; form.submit()">
	<br><div style="height: 6px;"></div>





<div style="height:7px;"></div>
<div style="float:left;">
	<fieldset style="white-space: nowrap;"><legend>Bounding box:</legend>
	<div style="float: left;">
	<input type="checkbox" name="rel" <?=($bbox[0]=="Rel")?'checked':''?>>Relative&nbsp;&nbsp;
	</div>
	<div style="float:left; text-align: center;">
N:<input type="text" name="latmax" class="middle" value="<?=$bbox[2]?>" size=12><br>
W:<input type="text" name="lngmin" class="middle" value="<?=$bbox[3]?>" size=12>&nbsp;&nbsp;&nbsp;<input type="text" name="lngmax" class="middle" value="<?=$bbox[4]?>" size=12>:E<br>
S:<input type="text" name="latmin" class="middle" value="<?=$bbox[1]?>" size=12>
	</div>
	</fieldset>
</div>
<fieldset><legend>Custom Plotted Parameter Settings</legend>
	plot title:&nbsp;<input size=25 type="text" name="title" value="<?=$bbox[11]?>">
	<div style="height:6px"></div>
	Parameter:&nbsp;<? /* <input size=5 type="text" name="paramcol" value="<?=$bbox[6]?>"> */ ?>
<?
	echo "<select name=\"paramcol\">\n";
	//$selopts="<option value=\"\">bathymetry</option>\n";
	if ($bbox[6] == "depthm") {
		$selopt="<option value=\"depthm\">bathymetry</option>\n";
	}
	$selopts.="<option value=\"depthm\">bathymetry</option>\n";
	//$selopt = "";
	if (file_exists("/opt/neeskay/data/bathysifields.csv")) {
		$fh = fopen("/opt/neeskay/data/bathysifields.csv","r");
		while(!feof($fh)) {
			$row = fgetcsv($fh);
			if ($row[0] != "") {
				$selstr = "<option value=\"".$row[0]."\" $sel>".$row[1]."</option>\n";
				if ($row[0]==$bbox[6]) $selopt = $selstr;
				$selopts .= $selstr;
			}
		}
		fclose($fh);
	}
	echo "$selopt$selopts";
	echo "</select>\n";
?>
<br>
	<div style="height:6px"></div>
	start date/time:<input size=20 type="text" name="start" value="<?=$bbox[7]?>"><br>
	end date/time:&nbsp;&nbsp;<input size=20 type="text" name="end" value="<?=$bbox[8]?>"><br>
	<div style="height:6px"></div>
	plot symbol size:<input size=8 type="text" name="cex" value="<?=$bbox[9]?>"><br>
	sampling lag:<input size=6 type="text" name="lag" value="<?=$bbox[10]?>">sec<br>
	z-scale: <input size=8 type=text name="zmin" value="<?=$bbox[12]?>"> -
	<input size=8 type=text name="zmax" value="<?=$bbox[13]?>"><br>
	<input type=checkbox name="mapping" value="1" <?= $bbox[14]==1 ? "checked" : "" ?> >Suppress Map Tiles (faster!)
</fieldset>
<br>
<input type="hidden" name="formaction" value="default">
<input type="button" name="saveonly" value="Save &amp; Apply Settings to Chart (but not to a Query Name)"
	onclick="form.formaction.value='saveonly'; openDialog('update from form...'); form.submit();">
</fieldset>
</fieldset>
</div>
</div>
</form>

<!-- ancient chinese secret -->
<iframe style="display:none" name="submittarget"></iframe>

<!-- dialogs -->

<!-- progress dialog -->
<div id="progressdialog" title="Please wait"><center>Your new map is being generated.  Please wait.<br><img src="gears.gif">
<div id="progresslog" style='white-space:pre; font-family: "Courier New",Courier,monospace; font-size: 12px; font-weight: bold; text-align: left; color:#CCFFCC; background-color:black; height:80px; width:300px; border: 1px solid black; overflow-x: hidden;'></div>
</center>
</div>

<!-- map click dialog -->
<div id="mapclick" title="Map Click" >
</div>

<!-- point info -->
<div id="pointinfo" title="Data Point Info" style="white-space: nowrap;">
</div>
<div id="mappoint" style="width:9;height:9;border:solid #ff00FF 3px;"></div>
</body>
</html>
