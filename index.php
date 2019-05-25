<!DOCTYPE html>
<html>
<head>
<title>CiteIt Beta</title>
<meta name="twitter:card" content="summary" />
<meta name="twitter:site" content="CiteIt" />
<meta name="twitter:title" content="CiteIt Beta" />
<meta name="twitter:description" content="Create citations without distractions on CiteIt" />
<link rel="stylesheet" type="text/css" href="main.css" />
</head>
<body>
<script src="js/jquery.min.js" type="text/javascript"></script>
<?php
include('header.php');
if(isset($_COOKIE["citationtype"]) || isset($_GET["format"])){
echo "
<script>
function reqListener () {
  console.log(this.responseText);
}
function fullinfo(){
	var urlToRequest = document.getElementById('url').innerText;
	var apiurl = 'api/siteinfo/';
	console.log(urlToRequest);
	var oReq = new XMLHttpRequest();
	oReq.addEventListener('load', reqListener);
	oReq.open('GET', apiurl + '?url=' + encodeURIComponent(urlToRequest));
	oReq.onload = function(e){
		document.getElementById('step2_loading').style.display = 'none';
		//document.getElementById('response').innerHTML = oReq.responseText;
		var json_data_for_citation = JSON.parse(oReq.responseText);
	//	document.getElementById('author_name').innerHTML = json_data_for_citation['author'];
		document.getElementById('author_last_name_span').innerHTML = json_data_for_citation['author_last'];
		document.getElementById('author_first_name_span').innerHTML = json_data_for_citation['author_first'];
		document.getElementById('title_span').innerHTML = json_data_for_citation['title'];
		document.getElementById('site_title_span').innerHTML = json_data_for_citation['sitename'];
		document.getElementById('publisher_span').innerHTML = json_data_for_citation['publisher'];
		document.getElementById('date_published_span').innerHTML = json_data_for_citation['date_published_pretty'];
		document.getElementById('date_accessed_span').innerHTML = json_data_for_citation['date_accessed_pretty'];
		document.getElementById('url_data').innerHTML = json_data_for_citation['url'];
		document.getElementById('step2').style.display = 'block';
	//	document.getElementById('').innerHTML = json_data_for_citation[''];
	}
	oReq.onerror = function(e){
		document.getElementById('step2_loading').style.display = 'none';
		document.getElementById('step2_error').style.display = 'block';
	}
	document.getElementById('step2_loading').style.display = 'block';
	oReq.send();
}
function svie(element, value){ //svie = set value if exist
	if (element == null) {
		console.log(element + ' does not exist');
	}
	else {
		element.innerHTML = value;
		console.log(element + ' set to ' + value);
	}
}
function finish(){
	var months = new Array(12);
	months[0] = 'January';
	months[1] = 'February';
	months[2] = 'March';
	months[3] = 'April';
	months[4] = 'May';
	months[5] = 'June';
	months[6] = 'July';
	months[7] = 'August';
	months[8] = 'September';
	months[9] = 'October';
	months[10] = 'November';
	months[11] = 'December';
	var author_last_element = document.getElementById('author_last');
	var author_first_element = document.getElementById('author_first');
	var title_element = document.getElementById('title');
	var site_title_element = document.getElementById('site_title');
	var publisher_element = document.getElementById('publisher');
	var date_published_element = document.getElementById('date_published');
	var date_accessed_element = document.getElementById('date_accessed');
	var url_data_element = document.getElementById('url_data');
	var author_first_initial_element = document.getElementById('author_first_initial');
	var year_element = document.getElementById('year');
	var day_element = document.getElementById('day');
	var month_long_element = document.getElementById('month_long');
	var month_accessed_short_element = document.getElementById('month_accessed_short');
	var day_accessed_element = document.getElementById('day_accessed');
	var year_accessed_element = document.getElementById('year_accessed');
	var month_element = document.getElementById('month');
	var month_accessed_element = document.getElementById('month_accessed');
	var month_short_element = document.getElementById('month_short');
	var month_accessed_long_element = document.getElementById('month_accessed_long');
	var author_first_name = document.getElementById('author_first_name_span').innerText;
	var author_last_name = document.getElementById('author_last_name_span').innerText;
	var title = document.getElementById('title_span').innerText;
	var site_title = document.getElementById('site_title_span').innerText;
	var publisher = document.getElementById('publisher_span').innerText;
	var date_published = document.getElementById('date_published_span').innerText;
	var date_accessed = document.getElementById('date_accessed_span').innerText;
	var author_first_initial = author_first_name.charAt(0);

	var date_parsed = new Date(Date.parse(date_published));
	var year = date_parsed.getUTCFullYear();
	var month = date_parsed.getUTCMonth() + 1;
	var day = date_parsed.getUTCDay();
	var month_long = months[month-1];
	var month_short = month_long.substring(0,3);

	var date_accessed_parsed = new Date(Date.parse(date_accessed));
	var year_accessed = date_accessed_parsed.getUTCFullYear();
	var month_accessed = date_accessed_parsed.getUTCMonth() + 1;
	var day_accessed = date_accessed_parsed.getUTCDay();
	var month_accessed_long = months[month_accessed-1];
	var month_accessed_short = month_accessed_long.substring(0,3);
	
	svie(author_last_element, author_last_name);
	svie(author_first_element, author_first_name);
	svie(title_element, title);
	svie(site_title_element, site_title);
	svie(publisher_element, publisher);
	svie(date_published_element, date_published);
	svie(date_accessed_element, date_accessed);
	svie(url_data_element, url_data.innerText);
	svie(author_first_initial_element, author_first_initial);
	svie(year_element, year);
	svie(month_element, month);
	svie(day_element, day);
	svie(month_long_element, month_long);
	svie(month_short_element, month_short);
	svie(year_accessed_element, year_accessed);
	svie(month_accessed_element, month_accessed);
	svie(day_accessed_element, day_accessed);
	svie(month_accessed_long_element, month_accessed_long);
	svie(month_accessed_short_element, month_accessed_short);
	document.getElementById('finalcitation').style.display = 'block';
}
</script>
<div contenteditable id='url'>";
/*$url = 'enter url here...';
if(isset($_GET['url'])){
	$url = $_GET['url'];
}
echo $url;*/
echo "</div><script>$('#url').keypress(function(e){if (e.which==13){fullinfo();}; return e.which != 13;}); $('.spantextarea').keypress(function(e){return e.which != 13;});</script>
<br />
<button class='submit_button' onclick='fullinfo();'>Cite It!</button>
<div id='maindata'>
";
$formats = ['mla8', 'mla7', 'apa'];
$format = $_GET['format'];
if (!$format){
	$format = $_COOKIE['citationtype'];
}
if(!in_array($format, $formats)){
	$format = 'invalid';
} else{
	echo "
<div id='step2_loading' style='display:none'>
Loading data (this may take a few seconds)
</div>
<div id='step2_error' style='display:none'>
There was an error in fetching data from the API. Sorry about that!
</div>
<div id='step2' style='display:none;'>
<div class='spantextareadiv'>Author First Name: <span class='spantextarea' contenteditable id='author_first_name_span'></span></div>
<div class='spantextareadiv'>Author Last Name: <span class='spantextarea' contenteditable id='author_last_name_span'></span></div>
<div class='spantextareadiv'>Title: <span class='spantextarea' contenteditable id='title_span'></span></div>
<div class='spantextareadiv'>Site Title: <span class='spantextarea' contenteditable id='site_title_span'></span></div>
<div class='spantextareadiv'>Publisher: <span class='spantextarea' contenteditable id='publisher_span'></span></div>
<div class='spantextareadiv'>Date Published: <span class='spantextarea' contenteditable id='date_published_span'></span></div>
<div class='spantextareadiv'>Date Accessed: <span class='spantextarea' contenteditable id='date_accessed_span'></span></div>
<button class='submit_button' onclick='finish();'>Finish Citation</button>
</div>
";
}
if ($format=='mla8'){
include('templates/mla8.php');
} elseif ($format=='mla7'){
include('templates/mla7.php');
} elseif ($format=='apa'){
include('templates/apa.php');
} else {
	echo "<strong>Invalid citation format requested.</strong>";
}
} else{
	echo "<button class='submit_button' onclick='document.cookie = \"citationtype=mla8\"; location.replace(\"?format=mla8\");'>MLA 8</button>";
	echo "<button class='submit_button' onclick='document.cookie = \"citationtype=mla7\"; location.replace(\"?format=mla7\");'>MLA 7</button>";
	echo "<button class='submit_button' onclick='document.cookie = \"citationtype=apa\"; location.replace(\"?format=apa\");'>APA</button>";
}
?>
</div>
</body>
</html>
