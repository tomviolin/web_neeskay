
if (!window.GraphSys) window.GraphSys = {};


GraphSys.FindPosition = function(oElement)
{
  if(typeof( oElement.offsetParent ) != "undefined")
  {
    for(var posX = 0, posY = 0; (!!oElement) && (oElement.tagName != "BODY" && oElement.tagName != "body" && oElement.offsetLeft &&  oElement.offsetTop && (oElement.offsetTop != 0 || oElement.offsetLeft != 0)); oElement = oElement.offsetParent)
    {
      posX += oElement.offsetLeft;
      posY += oElement.offsetTop;
    }
    return { x:posX, y:posY };
  }
  else
  {
    return { x:oElement.x, y:oElement.y };
  }
}

GraphSys.GetCoordinates = function(e,imgElement)
{
  var PosX = 0;
  var PosY = 0;
  var ImgPos;
  ImgPos = GraphSys.FindPosition(imgElement);
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
  PosX = PosX - ImgPos.x;
  PosY = PosY - ImgPos.y;

  return {x:PosX, y:PosY};

}

GraphSys.convertImageToPageXY = function(imgElement,imgOffsetX,imgOffsetY) {
	var PosX=0;
	var PosY=0;
	var ImgPos = GraphSys.FindPosition(imgElement);
	var scrollX = document.body.scrollLeft + document.documentElement.scrollLeft;
	var scrollY = document.body.scrollTop + document.documentElement.scrollTop;
	return {
		pageX:   ImgPos.x + imgOffsetX,
		pageY:   ImgPos.y + imgOffsetY,
		clientX: ImgPos.x + imgOffsetX - scrollX,
		clientY: ImgPos.y + imgOffsetY - scrollY
	};


      PosX = clientX ;//+ document.body.scrollLeft + document.documentElement.scrollLeft;
      PosY = clientY ;//+ document.body.scrollTop + document.documentElement.scrollTop;
  PosX = PosX + ImgPos.x;
  PosY = PosY + ImgPos.y;
  return {x:PosX, y:PosY};
}
