<?php
error_reporting(0);
/**
 * Created by PhpStorm.
 * User: Niklas
 * Description: Get YouTube Random Video
 */

// *** CONFIG *** //
$YouTubeAPIKey      = '';    // Get one here: https://console.developers.google.com/apis/api/youtube/overview
$ChannelID          = '';                   // YouTube Channel ID
$DateOfFirstVideo   = '2000-01-01T00:00:00Z';                       // RFC 3339 formatted date-time value
$FallbackVideoLink  = 'http://youtube.com/';                        // Fallback video link (e.g. API down)
$usecurl            = true;
$debug              = false;
// ***        *** //

if(empty($YouTubeAPIKey) || empty($ChannelID) || $DateOfFirstVideo == "2000-01-01T00:00:00Z") {
    die('Config is not set correctly.');
}
$dateExploded = explode('-', $DateOfFirstVideo);
$dateExplodedSecond = explode('T', $dateExploded[2]);
$publishedBeforeYear = rand($dateExploded[0],date('Y'));
if($debug) {
    echo 'First video was published before '.$dateExploded[0];
    echo '<br>Randomized year is: '.$publishedBeforeYear;
}
if($publishedBeforeYear == $dateExploded[0]) {
    if($debug) {
        echo '<br>Randomized year is in the same year where the first video was published...
              <br>Searching from '.$dateExploded[1].'.'.$dateExplodedSecond[0].'.'.$publishedBeforeYear.' until 31.12.'.$publishedBeforeYear;
    }
    $publishedBeforeMonth = str_pad(rand($dateExploded[1], 12), 2, '0', STR_PAD_LEFT);
    $publishedBeforeDay = str_pad(rand(intval($dateExplodedSecond[0]), 31), 2, '0', STR_PAD_LEFT);
    if($debug) {
        echo '<br>Randomized Month: '.$publishedBeforeMonth.', randomized day: '.$publishedBeforeDay;
    }
} else {
    if($debug) {
        echo '<br>Randomized year is <b>not</b> in the same year where the first video was published...
              <br>Randomizing Month from 1 - 12 and day from 1 - 31...';
    }
    $publishedBeforeMonth = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
    $publishedBeforeDay = str_pad(rand(1, 31), 2, '0', STR_PAD_LEFT);
    if($debug) {
        echo '<br>Randomized Month: '.$publishedBeforeMonth.', randomized day: '.$publishedBeforeDay;
    }
}
$publishedBefore = $publishedBeforeYear.'-'.$publishedBeforeMonth.'-'.$publishedBeforeDay;
if($debug) {
    echo '<br>Trying to find video published before '.$publishedBefore;
}
$url = 'https://www.googleapis.com/youtube/v3/search?part=snippet&channelId='.$ChannelID.'&maxResults=50&type=video&publishedAfter='.$DateOfFirstVideo.'&publishedBefore='.$publishedBefore.'T00:00:00Z&order=date&fields=etag%2Citems(id%2FvideoId%2Csnippet%2Ftitle)&key='.$YouTubeAPIKey;

if($debug) {
    echo '<br>Generated API url: '.$url;
}

if($usecurl) {
    if($debug) {
        echo '<br>Trying to load api with curl...';
    }
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url,
    ));
    $response = curl_exec($curl);
    curl_close($curl);
} else {
    if($debug) {
        echo '<br>Trying to load api with file_get_contents()...';
    }
    $response = file_get_contents($url);
}

$data = json_decode($response, true);

if(!$data) { // Api down?
    header("Location: ".$FallbackVideoLink);
    exit;
}

if($debug) {
    echo '<br>The following videos where found:<br>
        <table>';
    foreach ($data['items'] as $item) {
        echo '<tr>
                <td><b>'.$item['id']['videoId'].'</b></td>
                <td>'.$item['snippet']['title'].'</td>
            </tr>';
    }
    echo '
</table>';
}

$videocount = count($data['items']);
$randomvideo = rand(0, intval($videocount)-1);
if($debug) {
    echo '<br>Videocount: '.$videocount;
    echo '<br>Randomvideo number: '.$randomvideo;
}
$videoID = $data['items'][$randomvideo]['id']['videoId'];
if($debug) {
    echo '<br>Following VideoID found: "'.$videoID.'"';
}

if(empty($videoID)) {
    if(!$debug) {
        header("Location: ".$FallbackVideoLink);
    } else {
        echo '<br>Redirect to fallback: '.$FallbackVideoLink;
    }
    exit;
} else {
    if(!$debug) {
        header("Location: http://youtube.com/watch?v=".$videoID);
    } else {
        echo '<br>Redirect to random video: http://youtube.com/watch?v='.$videoID;
    }
    exit;
}
