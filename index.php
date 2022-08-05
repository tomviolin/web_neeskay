
<html>
<head>
<title>Ship Data Monitoring System</title>
<script language="Javascript" type="text/javascript" href="sarissa.js"></script>
<script language="Javascript">

var graphleftmargin = 55;
var graphrightmargin = 55;
var graphtopmargin = 30;
var graphbotmargin = 130;
var graphWidth=650;
var graphHeight=450;

var Interval = 5;
var graphRange = 60;  // initial defaults
var camLoaded = true;
var baseURL   = "/neeskay/trackingimg-nmea.php?width="+graphWidth+"&height="+graphHeight;

var starttime=escape(minutesago2str(graphRange));

//var last1URL  = baseURL + "&starttime="+escape(minutesago2str(graphRange))+"&dateformat=short";
var last1URL  = baseURL + "&starttime="+starttime+"&dateformat=short";
var currentURL = last1URL;
<?php
	if (isset($_GET['startdate'])) {
		$grRangeDefault = 5;
		echo "graphRange = $grRangeDefault;\n";
		echo "var  timeOffset = ";
		echo ((time() - strtotime($_GET['startdate']))/60)-(60+$grRangeDefault/2).";\n";
	} else {
		echo "var timeOffset = 0;\n";
		$grRangeDefault = 60;
	}

function rangeOption($range, $text) {
	global $grRangeDefault;
	echo "<option value=$range ". ( ($range==$grRangeDefault) ? "selected":"").">$text</option>\n";
}
?>
var timerID = 0;

function minutesago2str(mins) {

	minsago = Math.floor(mins);
	secsago = Math.floor((mins - minsago) * 60);
	
	if (secsago > 0) {
		return minsago + " minutes " + secsago + " seconds ago";
	} else {
		return minsago + " minutes ago";
	}
}
	



function FindPosition(oElement)
{
  if(typeof( oElement.offsetParent ) != "undefined")
  {
    for(var posX = 0, posY = 0; oElement; oElement = oElement.offsetParent)
    {
      posX += oElement.offsetLeft;
      posY += oElement.offsetTop;
    }
      return [ posX, posY ];
    }
    else
    {
      return [ oElement.x, oElement.y ];
    }
}

function GetCoordinates(e)
{
  var PosX = 0;
  var PosY = 0;
  var ImgPos;
  ImgPos = FindPosition(document.getElementById("cam"));
  if (!e) var e = window.event;
  if (e.pageX || e.pageY)
  {
    PosX = e.pageX;
    PosY = e.pageY;
  }
  else if (e.clientX || e.clientY)
    {
      PosX = e.clientX + document.body.scrollLeft
        + document.documentElement.scrollLeft;
      PosY = e.clientY + document.body.scrollTop
        + document.documentElement.scrollTop;
    }
  PosX = PosX - ImgPos[0];
  PosY = PosY - ImgPos[1];

  return [PosX, PosY];

}


function clickGraph(e) {

	if (graphRange == "all") {
		alert ("clicking does not currently work with 'all data' setting.");
		return false;
	}
	/*
	if (window.event) {
		// IE-like
		clickX = window.event.offsetX;
		clickY = window.event.offsetY;
		button = window.event.button;
	} else if (e.clientX && document.cam.x) {
		// Moz-like
		clickX = e.clientX-document.cam.x;
		clickY = e.clientY-document.cam.y;
		button = e.button;
	}
	*/


	var clickpos = GetCoordinates(e);
	clickX = clickpos[0];
	clickY = clickpos[1];

	//alert (clickX+","+clickY+"; "+button);
	
	// check if clicked outside bounds of graph
	if ((clickX < graphleftmargin) || (clickX > graphWidth - graphrightmargin)){
	//	alert(clickX + " outside bounds of "+graphleftmargin+
//					" to " + (graphWidth - graphrightmargin))
		return false;
	}
	
	graphpixelrange = (graphWidth - graphleftmargin - graphrightmargin);
	clickedfraction = (clickX - graphleftmargin) / graphpixelrange;

	clickedtime = Number(timeOffset) + Number(graphRange) - (graphRange * clickedfraction);
	theForm=document.graphform;
	thisRangeIndex = theForm.range.selectedIndex;
	if (thisRangeIndex > 0) {
		theForm.range.selectedIndex -= 1;
		thisRangeIndex -= 1;
	}
	newGraphRange = theForm.range.options[thisRangeIndex].value;
	
	newTimeOffset = clickedtime - Math.floor(newGraphRange/2);
	if (newTimeOffset < 0) newTimeOffset = 0;
	
	timeOffset = newTimeOffset;
	graphRange = newGraphRange;
	
	// alert("clickedtime="+clickedtime+" timeOffset="+timeOffset+" graphRange="+graphRange);
	setGraph(theForm);
	return false;
}

function setGraph(theForm) {

	graphRange = theForm.range.options[theForm.range.selectedIndex].value;

	if (graphRange == "all") {
		currentURL = baseURL + "&dateformat=medium&starttime=1970-1-1&endtime=";
		timeOffset = -1;
	} else {
		if (timeOffset == -1) {
			timeOffset = 0;
		}
		currentURL = baseURL + "&starttime=" + escape(minutesago2str( Number(graphRange) + Number(timeOffset) ));
		if (timeOffset > 0) {
			currentURL += "&endtime=" + escape(minutesago2str(timeOffset));
		}
		if (Number(graphRange) > 60*24*3 || (Number(timeOffset)+Number(graphRange)) > 12*60) {
			currentURL += "&dateformat=medium"
		} else {
			currentURL += "&dateformat=short"
		}
	}


	
//	if (theForm.fixedscale.checked) {
//		// set the scale
//		currentURL += "&scale=fixed&scalebot="+theForm.scalebot.value+"&scaletop="+theForm.scaletop.value;
//	}


	// handle depth
	depthmatched = false;
	for (i = 0; i < theForm.length; ++i) {
		if (theForm.elements[i].name.substr(0,8)=="depthbut") {
			if (theForm.elements[i].value == theForm.depthscale.value) {
				theForm.elements[i].style.background="#FF0000";
				theForm.elements[i].style.color="white";
				depthmatched = true;
			} else {
				theForm.elements[i].style.background="";
				theForm.elements[i].style.color="black";
			}
		}
	}
	if (isNaN(Number(theForm.depthscale.value)) ) {
		theForm.depthscale.value = "auto";
		theForm.depthbutauto.style.background="red";
	}

	if (theForm.depthscale.value == "auto") {
		theForm.depthplus5.disabled = true;
		theForm.depthminus5.disabled = true;
	} else {
		theForm.depthplus5.disabled = false;
		theForm.depthminus5.disabled = false;
	}
	if (theForm.depthscale.value > 0) {
		currentURL += "&depthscale=" + theForm.depthscale.value;
	}



	currentURL += "&title=" + escape("Temperature / Depth, scale: " + theForm.range.options[theForm.range.selectedIndex].text);

	theForm.next.disabled = (timeOffset <= 0);
	theForm.home.disabled = (timeOffset <= 0);
	theForm.prev.disabled = (theForm.range.options[theForm.range.selectedIndex].value == "all");

	camLoaded = true;
	startClock();

}

function changeRange(theForm) {
	if (graphRange == "all" || timeOffset < 0) {
		// previous graph was "all data", reset to most recent data
		timeOffset = 0;
	} else {
		// try to maintain left edge
		timeOffset = Number(timeOffset) + Number(graphRange) - Number(theForm.range.value);
		if (timeOffset < 0) timeOffset = 0;
	}
	setGraph(theForm);
}

function goDate(theForm) {
	now = new Date();
	trueDate = now.valueOf() - timeOffset*1000*60;
	//alert("trueDate = " + trueDate.toString() + "  timeOffset="+timeOffset);
	stamp = new Date(trueDate);
	datestring = prompt("Enter date: ", stamp.toLocaleString());
	//alert("datestring = " + datestring);
	newdate = new Date(datestring);
	//alert("newdate = " + newdate.toString() + " - now = " + now.toString());
	//alert("newdate = " + newdate.valueOf()  + " - now = " + now.valueOf());
	//alert("new timeOffset = " + (now.valueOf() - newdate.valueOf()));
	timeOffset = (now.valueOf() - newdate.valueOf()) / 60000;
	setGraph(theForm);
}



function goPrevious(theForm) {
	timeOffset += Math.floor(Number(theForm.range.value) / 2);
	setGraph(theForm);
}

function goNext(theForm) {
	timeOffset -= Math.floor(Number(theForm.range.value) / 2);
	if (timeOffset < 0) timeOffset = 0;
	setGraph(theForm);
}

function goHome(theForm) {
	timeOffset = 0;
	setGraph(theForm);
}

function changeScale(theForm) {
	theForm.scalebot.disabled=!theForm.fixedscale.checked;
	theForm.scaletop.disabled=!theForm.fixedscale.checked;
	theForm.setscale.disabled=!theForm.fixedscale.checked;
	setGraph(theForm);
}


function loadGraph() {
	camLoaded = true;
	startClock();
}


function startClock() {
      if (camLoaded) {
            document.cam.src=currentURL+"&rand=" + Math.random();
            camLoaded = false;
      }
      if (timerID != 0) {
           clearTimeout(timerID);
      }
      timerID = setTimeout("startClock()", 1000 * Interval)
}

function doDownload(a,maps) {
	x = new String(document.cam.src);
	newurl = document.cam.src + "&csvdownload=yes";
	//if (document.graphform.dlcalib.checked) {
	//	newurl += "&calibdata=yes";
	//}
	if (a >= 1) {
		newurl += "&path="+(a-0);
	}
	// alert('Downloading '+newurl);
	if (maps && maps == 1) {
		newurl = "esrifeature.php?query=" + escape(newurl+"&reallyskip=1");
		window.open(newurl);
	} else {
		document.location.href = newurl; //+"&reallyskip=10";
	}
	return void(0);
}

function setDepth(button) {
	button.form.depthscale.value = button.value;
	setGraph(button.form);
	return false;
}

function initPage() {
	setGraph(document.graphform);
}
</script>
<style type="text/css">
body,td { font-family:verdana,arial; font-size: 12px; }
a {text-decoration:none; color:blue; font-weight:bold;}
a:hover {color:#CC0000; text-decoration:underline;}
.control {font-size: 15pt;}
.depth {font-size: 15pt; width: 80px;}
/* a:visited {color:blue;} */
table.toolbar {
	border-width: 2px;
	border-style: outset;
	padding: 0px;
}
table.toolbar td {
	font-size: 8px; font-weight: normal; text-align: center;
	padding: 0px;
	width: 80px;
}
table.toolbar td a {
	font-weight: normal;
}

</style>
</head>
<body bgcolor="#ccccff" onload="initPage();">
<center>
<a name="top"></a><font face="verdana,arial,helvetica,sans-serif" size=5><b><span id=headline>
		</span></b></font>
</center>
<table border=0 width=650 align=center>
<form name="graphform">
<tr>
<td colspan=3 align=center valign=top>
<table border=0 cellpadding=1 cellspacing=0>
	<tr><td><img border=0 name="cam" id="cam" ismap src="blah" style="cursor:pointer"
			width=650 height=450 onload="camLoaded=true;" onmousedown="clickGraph"></td>
			<td width=0></td>
			<td align=center bgcolor="#ccffff"><Font style="font-size: 15px; font-weight: 800"><b>Depth<br>Display</b><br /></font>
			<button name="depthbut10" value="10" class="depth" onclick="return setDepth(this)">10m</button><br>
			<button name="depthbut20" value="20" class="depth" onclick="return setDepth(this)">20m</button><br>
			<button name="depthbut30" value="30" class="depth" onclick="return setDepth(this)">30m</button><br>
			<button name="depthbut50" value="50" class="depth" onclick="return setDepth(this)">50m</button><br>
			<button name="depthbut75" value="75" class="depth" onclick="return setDepth(this)">75m</button><br>
			<button name="depthbut100" value="100" class="depth" onclick="return setDepth(this)">100m</button><br>
			<button name="depthbut150" value="150" class="depth" onclick="return setDepth(this)">150m</button><br>
			<button name="depthbutauto" value="auto" class="depth" onclick="return setDepth(this)">auto</button><br>
			<button name="depthminus5" onclick="if (form.depthscale.value > 5) { form.depthscale.value -= 5.0; setGraph(form);} return false;" class="depth">-5</button>
			<button name="depthplus5" onclick="if (form.depthscale.value > 0) { form.depthscale.value = Number(form.depthscale.value) + 5; setGraph(form);} return false;" class="depth">+5</button>
			<br>Depth:
			<input type="text" name="depthscale" value="auto" size=4><br>
			<button name="customdepth" onclick="setGraph(form); return false;">Custom<br>Depth</button>
</td></tr>
<tr><td align=center>
<script language="Javascript">
	document.cam.onmousedown=clickGraph;
</script>
<input type="button" name="prev" value=" << Page" onclick="goPrevious(form)" class="control">
<select name=range onchange="changeRange(form)" class="control">
<?php
	rangeOption(2,"2 minutes");
	rangeOption(5,"5 minutes");
	rangeOption(10,"10 minutes");
	rangeOption(30,"30 minutes");
	rangeOption(60,"1 hour");
	rangeOption(90,"1.5 hours");
	rangeOption(180,"3 hours");
	rangeOption(360,"6 hours");
	rangeOption(720,"12 hours");
	rangeOption(1440,"24 hours");
	rangeOption(2160,"36 hours");
	rangeOption(10080,"1 week");
	rangeOption(20160,"2 weeks");
	rangeOption(40320,"4 weeks");
	rangeOption(241920,"24 weeks");
	rangeOption(525600,"1 year");
	rangeOption(525600*8,"8 years");
?>
</select>
<input type="button" name="next" value="Page >> " onclick="goNext(form)" class="control">
<input type="button" name="home" value="NOW ->|" onclick="goHome(form)" class="control">
<input type="button" name="date" value="date" onclick="goDate(form)" class="control">
<table border=0 class="toolbar" bgcolor=white><tr>
<td valign=middle><font size=2>Save Data As:</font></td>
<td valign=bottom><a href="javascript:doDownload();" title="Save CSV file of displayed data" alt="Save CSV file of displayed data"><img src="images/save_csv.gif" border=0><br>Excel CSV</a></td>
<td valign=bottom><a href="javascript:doDownload(1);" title="Download Google Earth KML file of ship's path" alt="Download Google Earth KML file of ship's path"><img src="images/gedownload.gif" border=0><br>KML</a></td>
<td valign=bottom><a href="javascript:void(doDownload(1,1));" title="Display ship's path in a ESRI Leaflet Map" alt="Display ship's path in an ESRI Leaflet Map"><img src="images/gmm.png" border=0><br>ESRI Leaflet Map</a></td>
<td valign=bottom><a href="javascript:doDownload(2);" title="Download Google Earth KML w/Depths" alt="Download Google Earth KML w/Depths"><img src="images/gedownload.gif" border=0><br>GE Points</a></td>
<!-- does not work: <a href="javascript:doDownload(2,1);">[Google Map]</a> -->
</tr></table>
<? // $host=$_SERVER['HTTP_HOST']; if ($host == 'neeskay.dyndns.org' || $host == "192.168.148.128" || $host=="localhost" || $host=="127.0.0.1" || $host == "neeskay.uits.uwm.edu") { ?>
<br>
<a href="../ysi/config-nmea.php">Configure YSI Sonde setup</a>
&nbsp;&nbsp;&nbsp;&nbsp;<a href="bathy.php">Dynamic Bathymetry Chart</a>
&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://<?=$host?>:81/uefish/">uEfish Sonar</a>
<? //} ?>
</td><td></td><td></td></tr></table>
</td>
</tr>
</form>
</table>
<script language="Javascript">

function timeString() {
	var timestr = new Date();
	var hourstr = timestr.getHours();
	var minutestr = timestr.getMinutes();
	minutestr=((minutestr < 10) ? "0" : "") + minutestr;
	var secondstr = timestr.getSeconds();
	secondstr=((secondstr < 10) ? "0" : "") + secondstr;
	var clock = hourstr + ":" + minutestr + ":" + secondstr;
	return clock;
}

var xmlhttp;
function checkError() {
	xmlhttp = new XMLHttpRequest();  
	hoststr = "showerr.php";
	xmlhttp.open("GET", hoststr, true);  
	xmlhttp.onreadystatechange = function() {  
		if(xmlhttp.readyState == 4)  
		//alert(new XMLSerializer().serializeToString(xmlhttp.responseXML));  
		var rtext = xmlhttp.responseText;
		if ((rtext) && (rtext[0] == 'E')) {
			headtext = "<font color=red>Data Error:<br><font size=2>" + xmlhttp.responseText + "</font></font>";
		} else {
			headtext = "Ship Data Monitoring System<br><font size=2><span id=\"datatime\">" + rtext + "</span><" + "/" + "font>";
		}
		document.getElementById("headline").innerHTML = headtext;
		// document.location.href="#top";
	};  
	// if needed set header information   
	// using the setRequestHeader method  
	xmlhttp.send('');
	setTimeout(checkError, 1000);  // every second
}
checkError();
</script>
</body>
</html>
