<?php
/*

Version: 2022.05.08.03.07

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

Modificators:
	
	&down
	do you like to download the audio or video? Add "&down" to the link and you can do as you please.
	
	&feed
	this is an inevitable modificator.
	
	&isok
	this bypasses the securitycheck of $allowed_feeds_array. You can use any feed you like; better use it for debug only. 
	
	&lang=de
	switches from English to German.
	
	&standalone
	I've designed the Podcast-Parser to have it included in an <iframe> Tag on my Homepage. If you need a standalone version, use &standalone in the link.
		
	Here's an example of all modificators used in one link:
	php_podcast_parser.php?&standalone&de&isok&down&feed=https%3A%2F%2Fkinder.wdr.de%2Fhoerensehen%2Fpodcast-maus-102.podcast
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
$how_many_items = -1; // set it to -1 for showing all items.

//Config #4: Allow bypass of security check in allowed_feeds_array by using the modificator 'isok' (see information above) ?
$allow_every_feed = false; // set to TRUE if you want to allow bypassing the security feature.

//Below is the language configuration.
$name_authors = "Authors";
$name_link_episode = "Episode Link";
$name_podcast_description = "Podcast-Description";
$name_podcast_authors = "Podcast-Authors";
$name_link_to_the_podcast = "Link to the Podcast";
$name_link_to_the_feed = "Link to the Feed";
$name_personal_note = "Personal note: This is a \"RSS parser\" that visually edits a podcast file (xml) and forms a text from machine text that is readable by humans. All texts and files belong to the respective creators and authors of the RSS feed. These are linked or named above.";
$name_legal_explanation = "This Podcast Reader is available under a <a href=\"https://unlicense.org/\">\"The Unlicence\" (CC0 Licence)</a> on <a href=\"https://github.com/kyberspatz/php_podcast_parser\">kyberspatz@github</a>";

if(isset($_GET['lang']))
{
	$lang = trim($_GET['lang']);
		if($lang = "de")
		{
		$name_authors = "Autoren";
		$name_link_episode = "Link zur Episode";
		$name_podcast_description = "Podcast-Beschreibung";
		$name_podcast_authors = "Podcast-Autor(en)";
		$name_link_to_the_podcast = "Link zum Podcast";
		$name_link_to_the_feed = "Link zum Feed";
		$name_personal_note = 'Persönlicher Hinweis: Dies ist ein "RSS-Parser", der aus dem XML-Code eines XML-Feeds (Podcast) einen lesbaren Code für Menschen macht. Die hier genannten Texte, Bilder und Dateien werden nur geparsed, nicht zu eigen gemacht. Alle Rechte gehören den jeweilig genannten Autoren und verlinkten Webseiten.';
		$name_legal_explanation = 'Der Code des PHP-RSS-Parser ist frei verfügbar unter einer <a href="https://unlicense.org/">"The Unlicence" (CC0 Lizenz = Gemeinfrei)</a> bei <a href="https://github.com/kyberspatz/php_podcast_parser">kyberspatz@github</a>';
		}
}
								
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

audio {width:100%;max-width:100%;}

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
	<?php if(isset($_GET['standalone'])){echo 'width:50%;margin-left:25%;';} else {echo 'margin-left:2.5%;width:95%;';} ?>
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
	
	
		if(!in_array($feedrequest,$allowed_feeds_array))
		{		
				
		if($allow_every_feed == false)
			{		
				$errors[] = "Sorry, this feed is not allowed. Please set it up in the sourcecode of the script.";
				GOTO END;	
			}
	
		  if($allow_every_feed == true)
		  {
			  if(!isset($_GET['isok']))
			  {
				 $errors[] = "Sorry, this feed is not allowed. Please set it up in the sourcecode of the script.";
				GOTO END;	
			  }
			}			
		}
	}

//if you're debugging, the script will skip the previous lines and start here.
DEBUG:

//comment the next line out by adding // at the beginning, if you want to have a double_check, if the file does exist.
$feed_file = file_get_contents($feedrequest);
//var_dump($feed_file);exit;

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
$feed['header'] = substr($feed_file,0,strpos($feed_file,"<item"));
$feed['body'] = substr($feed_file,strpos($feed_file,"<item"));

$items = explode("<item",$feed['body']);

//Now we're slicing the array to the desired length
if($how_many_items > -1)
	{
		$items = array_slice($items,1,($how_many_items+1));
	}

$headertext = str_replace("\n","",$feed['header']);

//Podcast-Title	
	$search1 = "<title>";
	$search2 = "</title>";
	$search = substr($headertext,strpos($headertext,$search1)+strlen($search1));
	$podcast_title = substr($search,0,strpos($search,$search2));
	$podcast_title = str_replace("<![CDATA[","",$podcast_title);
	$podcast_title = str_replace("]]>","",$podcast_title);
	$podcast_title = str_replace(">>",">",$podcast_title);
	$header['title'] = $podcast_title;

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
		$episode_title = substr($search,0,strpos($search,$search2));
		$episode_title = str_replace("<![CDATA[","",$episode_title);
		$episode_title = str_replace("]]>","",$episode_title);
		$episode_title = str_replace(">>",">",$episode_title);
		$episode[$a]['title'] = $episode_title;
		
	//Description	
		$search1 = "<description>";
		$search2 = "</description>";
		$search = substr($item,strpos($item,$search1)+strlen($search1));
		$description = substr($search,0,strpos($search,$search2));
		$description = str_replace("<![CDATA[","",$description);
		$description = str_replace("]]>","",$description);
		$description = str_replace(">>",">",$description);
		$episode[$a]['description'] = $description;
		
	//Episode Authors	
		$search1 = "<itunes:author>";
		$search2 = "</itunes:author>";
		$search = substr($item,strpos($item,$search1)+strlen($search1));
		if(strpos($item,$search1) !== false)
		{
			$episode_authors = substr($search,0,strpos($search,$search2));
			$episode_authors = str_replace("<![CDATA[","",$episode_authors);
			$episode_authors = str_replace("]]>","",$episode_authors);
			$episode_authors = str_replace(">>",">",$episode_authors);
			$episode[$a]['episode_authors'] = $episode_authors;
		}
		
		
	//link	
		$search1 = "<link>";
		$search2 = "</link>";
		$search = substr($item,strpos($item,$search1)+strlen($search1));
		$episodelink = substr($search,0,strpos($search,$search2));
		$episodelink = str_replace("<![CDATA[","",$episodelink);
		$episodelink = str_replace("]]>","",$episodelink);
		$episodelink = str_replace(">>",">",$episodelink);
		$episode[$a]['episodelink'] = $episodelink;	
		
	//pubDate	
		$search1 = "<pubDate>";
		$search2 = "</pubDate>";
		$search = substr($item,strpos($item,$search1)+strlen($search1));
		$pubDate[$a] = substr($search,0,strpos($search,$search2));
		$episode[$a]['pubDate'] = date($dateconfig,strtotime($pubDate[$a]));
		
		
		//pubDate -> dc:date
		$search1b = "<dc:date>";
		$search2b = "</dc:date>";
		if(strpos($item,$search1b) !== false)
		{
			$searchb = substr($item,strpos($item,$search1b)+strlen($search1b));
			$pubDate[$a] = substr($searchb,0,strpos($searchb,$search2b));
			$episode[$a]['pubDate'] = date($dateconfig,strtotime($pubDate[$a]));	
		} else 
		{
			if(!strpos($item,$search1b) && !strpos($item,$search1)){
				$episode[$a]['pubDate'] = "";
			}
		}
		
		
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
		//$filetype = substr($output['media'],strrpos($output['media'],"."));
		if(strpos($output['media'],".mp3")!==false)
		{
			echo "<p><audio src=\"".$output['media']."\" controls preload=\"none\" ".$prevent_download."></audio></p>";
		}
		if(strpos($output['media'],".m4a")!==false)
		{
			echo "<p><audio src=\"".$output['media']."\" controls preload=\"none\" ".$prevent_download."></audio></p>";
		}
		if(strpos($output['media'],".wav")!==false)
		{
			echo "<p><audio src=\"".$output['media']."\" controls preload=\"none\" ".$prevent_download."></audio></p>";
		}
	if(strpos($output['media'],".mp4")!==false)
		{
// Now we're beautifying the video player by giving it round edges by adding 
		echo "<p><video style=\"border-radius:10px;max-width:100%;\" src=\"".$output['media']."\" controls preload=\"none\" ".$prevent_download."></video></p>";
		}
		
	echo "<p class='podcast-item-beschreibung'>".$output['description']."</p>";
	if(isset($output['episode_authors']))
		{
			echo "<p>".$name_authors.": ".$output['episode_authors']."</p>";
		}
	$linkto = '<a href="'.$output['episodelink'].'" target="_blank">['.$name_link_episode.']</a>';
	echo "<p>".$linkto."</p>";
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
	if(strpos($headertext,$search1) !== false){
	$search = substr($headertext,strpos($headertext,$search1)+strlen($search1));
	$header['author'] = substr($search,0,strpos($search,$search2));
	}

//And now it's time for a footer.
echo "<p>".$name_podcast_description.": ".$header['description']."</p>";
echo "<hr>";
if(isset($header['author']))
	{
		echo "<p>".$name_podcast_authors.": ".$header['author']."</p>";
	}
$linkto = '<a href="'.$header['link'].'" target="_blank">'.$header['link'].'</a>';
echo "<p>".$name_link_to_the_podcast.": ".$linkto."</p>";
$linkto = '<a href="'.$feedrequest.'" target="_blank">'.$feedrequest.'</a>';
echo "<p>".$name_link_to_the_feed.": ".$linkto."</p>";
echo "<hr><p>".$name_personal_note."</p>";
echo "<p>".$name_legal_explanation."</p>";

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
