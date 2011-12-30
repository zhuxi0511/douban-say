function getXMLHTTPRequest()
{
	try
	{
		req = new XMLHttpRequest();
	}
	catch(err1)
	{
		try
		{
			req = new ActiveXObject("Msxm12.XMLHTTP");
		}
		catch(err2)
		{
			try
			{
				req = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch(err3)
			{
				req = false;
			}
		}
	}
	return req;
}

var http = getXMLHTTPRequest();
var name = "zhuxi0511";

function getDoubanSay_tmp()
{
	var myurl = "http://api.douban.com/people/ahbei";
	/*var myRand = parseInt(Math.random() * 999999999);
	var modurl = myurl + "?rand=" + myRand;*/
	http.open("GET", myurl, true);

	http.onreadystatechange = useHttpResponse;
	http.send(null);
}

function useHttpResponse()
{
	if ( http.readyState == 4 )
	//{
		if ( http.status == 200 )
		{
			/*var timeValue = http.responseXML.getElementsByTagName("timenow")[0];*/
			/*var peopleLocation = http.responseXML.getElementsByTagName("db:location");*/
			var peopleContent = http.responseXML.getElementsByTagName("content")[0];
			//document.write(peopleContent);
			document.write("good here");
			/*document.getElementById('showtime').innerHTML = timeValue.childNodes[0].nodeValue;*/
		}
	//}
	//else
	//{
		//document.getElementById('showtime').innerHTML = 'Loading...';
	//}
}

function getDoubanSay()
{
	document.getElementById('doubansay').innerHTML = '<div id="profile"><div class="infobox"><div class="ex1"><span></span></div><div class="bd"><img src="http://img3.douban.com/icon/ul51146110-9.jpg" class="userface" alt="" /><div class="sep-line"></div><div class="user-info">常居:&nbsp;<a href="http://www.douban.com/location/haerbin/">黑龙江哈尔滨</a><br /><div class="pl">zhuxi0511 <br/> 2011-04-26加入</div></div><div class="sep-line"></div><div class="user-intro"><div id="edit_intro"  class="j edtext pl"><span id="intro_display" >a good place</span></div></div></div><div class="ex2"><span></span></div></div></div>';
	var myurl = "http://api.douban.com/people/zhuxi0511";

	http.open("GET", myurl,  true );

	http.onreadystatechange = useHttpResponse;
	http.send(null);
}
