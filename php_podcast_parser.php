<?php
/*
This is free and unencumbered software released into the public domain.

Anyone is free to copy, modify, publish, use, compile, sell, or
distribute this software, either in source code form or as a compiled
binary, for any purpose, commercial or non-commercial, and by any
means.

In jurisdictions that recognize copyright laws, the author or authors
of this software dedicate any and all copyright interest in the
software to the public domain. We make this dedication for the benefit
of the public at large and to the detriment of our heirs and
successors. We intend this dedication to be an overt act of
relinquishment in perpetuity of all present and future rights to this
software under copyright law.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

For more information, please refer to <http://unlicense.org/>
*/

/*
USAGE: php_podcast_parser.php?feed=[url-encoded-FEED-URL]
EXAMPLE: php_podcast_parser.php?feed=https%3A%2F%2Fkinder.wdr.de%2Fhoerensehen%2Fpodcast-maus-102.podcast
*/

//Config #1: This is how the date of each episode will be formated. Adjust it to your needs.
$dateconfig = "d.m.Y"; // day.month.Year

//Config #2: To prevent misusage of your script, please name the allowed feed-URLs below.
$allowed_feeds_array = array
	(
	'https://www1.wdr.de/mediathek/audio/wdr5/wdr5-satire-deluxe/satiredeluxe100.podcast',
	'https://www1.wdr.de/mediathek/audio/hoerspiel-speicher/wdr_hoerspielspeicher150.podcast',
	'https://www1.wdr.de/mediathek/audio/wdr2/wdr2-kabarett/kabarett-podcast-100.podcast',
	'https://feeds.br.de/hoerspiel-pool/feed.xml',
	'https://kinder.wdr.de/hoerensehen/podcast-maus-102.podcast',
	);

//Config #3: How many items of the podcast do you like to have displayed?
$how_many_items = 5; // set it to -1 for showing all items.
								
?><!DOCTYPE html>
<html>
<title>PHP Podcast Parser</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<!--https://css-tricks.com/emoji-as-a-favicon/-->
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎧</text></svg>">
<!-- Here we're including a simple and pretty good modern css-framework: W3.CSS -->
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<style>
/* Put additional CSS-style here */

body 
	{
	/* Thanks to https://cssgradient.io/ */
	background: #2b5876;  /* fallback for old browsers */
	background: -webkit-linear-gradient(to right, #4e4376, #2b5876);  /* Chrome 10-25, Safari 5.1-6 */
	background: linear-gradient(to right, #4e4376, #2b5876); /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
	}
	
	
.output
	{
	border-radius: 10px;
	background-color:rgba(255,255,255,0.4);
	width:50%;
	margin-left:25%;
	margin-top:5%;
	margin-bottom:5%;
	word-break: break-word;
}


@media screen and (max-width: 600px)
	{
  .output 
	  {
	   width: 95%;
		margin-left:2.5%;
	  }	
	}		
</style>
<body>
<div class="w3-container output">
<?php
//DEBUG: Need to Debug sth.? Download the desired file and include it here and delete the // Slashes.
//$feedrequest= "podcast-maus-102.podcast"; GOTO DEBUG;

$errors = array();
//if there's no GET-request named 'feed', just exit.
if(!isset($_GET['feed']))
	{
	$errors[] = "Please specify a feed.<br>Example: <a href=\"php_podcast_parser.php?feed=https%3A%2F%2Fkinder.wdr.de%2Fhoerensehen%2Fpodcast-maus-102.podcast\">php_podcast_parser.php?feed=https%3A%2F%2Fkinder.wdr.de%2Fhoerensehen%2Fpodcast-maus-102.podcast</a>";
	GOTO END;
	} 

//if there's a GET-request named 'feed'
else 
	{
	$feedrequest = trim($_GET['feed']);
	
	//to prevent misusage of the script, the feed-URL must be allowed above in the $allowed_feeds_array.
	if(!in_array($feedrequest,$allowed_feeds_array)){		
		$errors[] = "Sorry, this feed is not allowed. Please set it up in the sourcecode of the script.";
	GOTO END;	} 
}

//if you're debugging, the script will skip the previous lines and start here.
DEBUG:

//comment the next line by adding // at the beginning, if you want to have a double_check, if the file does exist.
$feed_file = file_get_contents($feedrequest);

if(!isset($feed_file))
	{
	$headers = get_headers($feedrequest);
	$file_found = stristr($headers[0], '200');

	if($file_found == false)
	{
		$errors[] = "Sorry, this feed URL does not seem to point to a valid file.";
		GOTO END;
	} 
	else 
	{ 
		$feed_file = file_get_contents($feedrequest);
	}
	}

//Separates the header from the body (which contains the <items>)
$feed['header'] = substr($feed_file,0,strpos($feed_file,"<item>"));
$feed['body'] = substr($feed_file,strpos($feed_file,"<item>"));

$items = explode("<item>",$feed['body']);

//Now we're slicing the array to the desired length
if($how_many_items > -1)
	{
		$items = array_slice($items,2,($how_many_items+1));
	}

$headertext = str_replace("\n","",$feed['header']);

//Podcast-Title	
	$search1 = "<title>";
	$search2 = "</title>";
	$search = substr($headertext,strpos($headertext,$search1)+strlen($search1));
	$header['title'] = substr($search,0,strpos($search,$search2));

$episode = array();

for($a=0;$a<count($items)-1;$a++)
{
	if(!empty($items[$a]))
	{
		$item = str_replace("\n","",$items[$a]);
		
	//Title	
		$search1 = "<title>";
		$search2 = "</title>";
		$search = substr($item,strpos($item,$search1)+strlen($search1));
		$episode[$a]['title'] = substr($search,0,strpos($search,$search2));
		
	//Description	
		$search1 = "<description>";
		$search2 = "</description>";
		$search = substr($item,strpos($item,$search1)+strlen($search1));
		$description = substr($search,0,strpos($search,$search2));
		$description = str_replace("<![CDATA[","",$description);
		$description = str_replace("]]>",">",$description);
		$episode[$a]['description'] = $description;
		
	//pubDate	
		$search1 = "<pubDate>";
		$search2 = "</pubDate>";
		$search = substr($item,strpos($item,$search1)+strlen($search1));
		$pubDate[$a] = substr($search,0,strpos($search,$search2));
		$episode[$a]['pubDate'] = date($dateconfig,strtotime($pubDate[$a]));
		
	//Media	
		$search1 = "url=\"";
		$search2 = "\"";
		$search = substr($item,strpos($item,$search1)+strlen($search1));
		$episode[$a]['media'] = substr($search,0,strpos($search,$search2));
					
	//Uncomment these lines for making some Debug Experiments
	//print_r($episode[$a]);
	//print_r($items[$a]);
	}
}

////////////// OUTPUT //////////////
echo "<h1>".$header['title']."</h1>";

foreach($episode as $output)
{
//Edit the class-names required to your CSS File
	echo "<span class='podcast-item-datum'>".$output['pubDate']."</span><br>";
	echo "<h3 class='podcast-item-title'>".$output['title']."</h3>";
	
//If you're adding a "&down" at the feed-URL in the RSS-Parser, you can download the files. It's prevented by default.
	if(!isset($_GET['down']))
	{
		$prevent_download = "controlsList=\"nodownload\"";
	} 
	else 
	{
		$prevent_download = "";
	}
	
// Now it's time to figure out whether the media-url is a .mp3 or a .mp4, to show the audio-player or video-player.	
		$filetype = substr($output['media'],strrpos($output['media'],"."));
		if($filetype == ".mp3")
		{
			echo "<p><audio src=\"".$output['media']."\" controls preload=\"none\" ".$prevent_download."></audio></p>";
		}
	elseif($filetype == ".mp4")
		{
// Now we're beautifying the video player by giving it round edges by adding 
		echo "<p><video style=\"border-radius:10px;max-width:100%;\" src=\"".$output['media']."\" controls preload=\"none\" ".$prevent_download."></video></p>";
		}
		
	echo "<p class='podcast-item-beschreibung'>".$output['description']."</p>";
	echo "<hr>";
	
}

// Now its time to care about the header.

//Link	
	$search1 = "<link>";
	$search2 = "</link>";
	$search = substr($headertext,strpos($headertext,$search1)+strlen($search1));
	$header['link'] = substr($search,0,strpos($search,$search2));
	
//Description	
	$search1 = "<description>";
	$search2 = "</description>";
	$search = substr($headertext,strpos($headertext,$search1)+strlen($search1));
	$header['description'] = substr($search,0,strpos($search,$search2));
	
//Authors	
	$search1 = "<itunes:author>";
	$search2 = "</itunes:author>";
	$search = substr($headertext,strpos($headertext,$search1)+strlen($search1));
	$header['author'] = substr($search,0,strpos($search,$search2));

//And now it's time for a footer.
echo "<p>Podcast-Description: ".$header['description']."</p>";
echo "<p>Author(s): ".$header['author']."</p>";
$verlinkung = '<a href="'.$header['link'].'" target="_blank">'.$header['link'].'</a>';
echo "<p>Link to the Podcast: ".$verlinkung."</p>";
$verlinkung = '<a href="'.$feedrequest.'" target="_blank">'.$feedrequest.'</a>';
echo "<p>Link to the RSS-Feed: ".$verlinkung."</p>";
echo "<hr><p>Personal note: This is a \"RSS parser\" that visually edits a podcast file (xml) and forms a text from machine text that is readable by humans. All texts and files belong to the respective creators and authors of the RSS feed. These are linked or named above.</p>
<p>This Podcast Reader is available under a <a href=\"https://unlicense.org/\">\"The Unlicence\" (CC0 Licence)</a> on <a href=\"https://github.com/kyberspatz/php_podcast_parser\">kyberspatz@github</a></p>";

END:
// Error output
if(!empty($errors)){
	foreach($errors as $error_output)
	{
		echo $error_output."<br>";
	}
}
?>
</body>
</html>
