<?php
//dependancies
require('utils.php');

//supports either XML or JSON, but JSON by default

//HEADERS
//check if XML
if ($_GET['format']=="xml"){
    header('Content-Type:text/xml'); //the xml is human readable, to text/xml is used as opposed to application/xml
} else{
    header('Content-Type:application/json');
}
//END HEADERS

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
/*
GET /api/siteinfo.php?site=URL&format=(XML|JSON)
*/

//thank you https://davidwalsh.name/curl-download
function get_data($url) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_CONNECTIONTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
}

if (isset($_GET['url'])){
	$url_clean = str_replace("\n", "", $_GET['url']);
	$url_clean = str_replace("\r", "", $url_clean);
$webpage_raw = get_data($url_clean);
//GET WEBPAGE CONTENT
//TODO: follow redirects
//thank you https://www.binarytides.com/php-tutorial-parsing-html-with-domdocument/
$dom = new DOMDocument;
$dom->loadHTML($webpage_raw); //$html will be the contents of the webpage submitted
$dom->preserveWhiteSpace = false;
//END WEBPAGE CONTENT
//function GET FROM ELEMENT AND ATTRIBUTE OR NONE (that's a jumble of words)
function gwpc($element, $attribute, $attribute_content, $content){
//element = element to search
//attribute = attribute to search
//attribute_content = attribute content necessary to match
//content = attribute to return (or if it's "False", return the content)
global $dom;
$elements = $dom->getElementsByTagName($element);
for ($i = 0; $i < $elements->length; $i++){
	$element_to_check = $elements->item($i);
	if ($element_to_check->getAttribute($attribute) == $attribute_content){
		$element_with_attribute = $i;
		break;
	}
}
if (!isset($element_with_attribute)){
	return false;
} else{
	if ($content == false){
		return $elements->item($element_with_attribute)->nodeValue;
	} else {
		return $elements->item($element_with_attribute)->getAttribute($content);
	}
}
}

$data_array = [];

//GET TITLE
$e_pagetitle = $dom->getElementsByTagName('title');
if(isset($e_pagetitle)){
	$page_title = $e_pagetitle[0]->nodeValue; //use first <title> element
}/*
foreach ($e_pagetitle as $p){
	$page_title = utf8_decode($p->nodeValue);
}*/

//if there's a " - ", title is first part only
if (preg_match('/ - /', $page_title)){
	$title_step_1 = preg_split('/ - /', $page_title);
	//possible sitename
	$possible_sitename_from_title = $title_step_1[count($title_step_1)-1];
	unset($title_step_1[count($title_step_1)-1]); //remove sitename from title
	$title = '';
	foreach ($title_step_1 as $title_chunk){
		$title = $title.$title_chunk.' - ';
	}
	$title = substr($title, 0, -3);
} elseif (preg_match('/ \| /', $page_title)) { // same as above with | but with a notable difference: if there are multiple pipes, only the content before the first one is used (as opposed to all the content before the last one)
	$title_step_1 = preg_split('/ \| /', $page_title);
	//possible sitename
	$possible_sitename_from_title = $title_step_1[count($title_step_1)-1];
	$title = '';
	foreach ($title_step_1 as $title_chunk){
		$title = $title.$title_chunk.' | ';
	}
	$title = substr($title, 0, -3);
} else {
	$title = $page_title;
}
//replace newlines with spaces
$title = implode(' ', preg_split('/\n/', $title));

$data_array['title'] = $title;
//END TITLE
//GET SITE NAME
//check for og:site_name
//$sitename = get_from_meta_or_none("og:site_name");
$sitename = gwpc('meta', 'property', 'og:site_name', 'content');
if (!$sitename){ //if the tag isn't there, returns False
	//check for sitename in title
	if (isset($possible_sitename_from_title)){
		$sitename = $possible_sitename_from_title;
	}
}
if (ucfirst($sitename) == $possible_sitename_from_title){ //thanks for not capitalizing og:site_name, "the Guardian"
	$sitename = ucfirst($sitename);
}
$data_array['sitename'] = $sitename;
//END SITE NAME

//GET PUBLISHER
//currently, the same as sitename, but could change
$publisher = $sitename;
$data_array['publisher'] = $publisher;
//END PUBLISHER

//GET DATE PUBLISHED
$date_published = gwpc('meta', 'property', 'article:published', 'content');
//$date_published = get_from_meta_or_none("article:published"); //works on New York Times
if (!$date_published){
	$date_published = gwpc('meta', 'property', 'article:published_time', 'content'); //from The Guardian
}
if (!$date_published){
	$date_published = gwpc('data', 'property', 'dt-published', 'value'); //from Mastodon
}
$data_array['date_published'] = $date_published;
//also return a pretty formatted date, for easier use
if($date_published){
    $data_array['date_published_pretty'] = date("d M. Y", strtotime($date_published));
    $data_array['date_published_dict']['d'] = date("d", strtotime($date_published));
    $data_array['date_published_dict']['M'] = date("M", strtotime($date_published));
    $data_array['date_published_dict']['Y'] = date("Y", strtotime($date_published));
} else{
	$data_array['date_published_pretty'] = '';
}
//END DATE PUBLISHED
//GET DATE ACCESSED
$data_array['date_accessed'] = date("c");
$data_array['date_accessed_pretty'] = date("d M. Y");
$data_array['date_accessed_dict']['d'] = date("d");
$data_array['date_accessed_dict']['M'] = date("M");
$data_array['date_accessed_dict']['Y'] = date("Y");
//END DATE ACCESSED
//GET AUTHOR
//oh boy, there's NO standards on this, so I'll just do what *probably* will work
$author = gwpc('meta', 'property', 'author', 'content'); //from The Guardian, do NOT use og:author as that is a link to the author (which isn't what's wanted here)
if (!$author){
	$author_byline = gwpc('meta', 'property', 'byl', 'content');
	$byline_without_by = substr($author_byline, 3);
	$byline_split = preg_split('/and/', $byline_without_by);
	$author = $byline_split[0];
}
if (!$author){
	$author =gwpc('span', 'class', 'attr-fullname', false); //twitter
}
if (!$author){
	$author = gwpc('span', 'class', 'display-name__account', false); //mastodon
}
$data_array['author'] = $author;
//should also return last and first names (and middle initial)
$author_array = preg_split("/ /", $author);
if (sizeof($author_array) == 2){
	$author_first = $author_array[0];
	$author_last = $author_array[1];
} elseif (sizeof($author_array == 3)){
	$author_first = $author_array[0];
	if ((strlen($author_array[1] == 1) || ((strlen($author_array[1])==2) && ($author_array[1][1] == ".")))){ //second part is middle initial
		$author_last = $author_array[2];
		$author_middle = $author_array[1];
	} else {
		$author_last = $author_array[1].$author_array[2];
	}
} elseif (sizeof($author_array == 4)){
	$author_first = $author_array[0];
	if ((strlen($author_array[1] == 1) || ((strlen($author_array[1])==2) && ($author_array[1][1] == ".")))){ //second part is middle initial
		$author_last = $author_array[2].$author_array[3];
		$author_middle = $author_array[1];
	} else {
		$author_last = $author_array[1].$author_array[2].$author_array[3];
	}
}
$data_array['author_last'] = $author_last;
$data_array['author_first'] = $author_first;
$data_array['author_middle'] = $author_middle;
//END AUTHOR

////END INFO EXTRACTION

//add URL to data (for ease of use in certain API models)
$data_array['url'] = strip_tags($_GET['url']);

//return data
//change array to utf8
//THANK YOU https://www.virendrachandak.com/techtalk/how-to-apply-a-function-to-every-array-element-in-php
array_walk_recursive($data_array, function(&$value){
	$value = utf8_decode(trim($value));
});
//check if XML
if ($_GET['format']=="xml"){
    $xml_data = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
    array_to_xml($data_array,$xml_data);
    echo $xml_data->saveXML();
} else{
	echo json_encode($data_array);
}
}
else{
	echo '{"response": "error", "error": "INVALID_URL"}';
}
//echo $e_pagetitle;
//$pagetitle = $e_pagetitle->nodeValue;
//echo $pagetitle;

//echo "hi";
?>
