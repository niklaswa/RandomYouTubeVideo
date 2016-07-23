<?php
error_reporting(0);
/**
 * Created by PhpStorm.
 * User: Niklas
 * Description: Get YouTube Random Video
 */

// *** CONFIG *** //
$YouTubeAPIKey          = '';    // Get one here: https://console.developers.google.com/apis/api/youtube/overview
$ChannelID              = '';                   // YouTube Channel ID
$DateOfFirstVideo   = '2000-01-01T00:00:00Z';                       // RFC 3339 formatted date-time value
$FallbackVideoLink  = 'http://youtube.com/';                        // Fallback video link (e.g. API down)
// ***        *** //

$dateExploded = explode('-', $DateOfFirstVideo);
$dateExplodedSecond = explode('T', $dateExploded[2]);
$publishedBeforeYear = rand($dateExploded[0],date('Y'));
if($publishedBeforeYear == $dateExploded[0]) {
    $publishedBeforeMonth = str_pad(rand($dateExploded[1], 12), 2, '0', STR_PAD_LEFT);
    $publishedBeforeDay = str_pad(rand(intval($dateExplodedSecond[0]), 31), 2, '0', STR_PAD_LEFT);
} else {
    $publishedBeforeMonth = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
    $publishedBeforeDay = str_pad(rand(1, 31), 2, '0', STR_PAD_LEFT);
}
$publishedBefore = $publishedBeforeYear.'-'.$publishedBeforeMonth.'-'.$publishedBeforeDay;
$url = 'https://www.googleapis.com/youtube/v3/search?part=snippet&channelId='.$ChannelID.'&maxResults=50&type=video&publishedAfter='.$DateOfFirstVideo.'&publishedBefore='.$publishedBefore.'T00:00:00Z&order=date&fields=etag%2Citems%2Fid&key='.$YouTubeAPIKey;
$data = json_decode(file_get_contents($url), true);

if(!$data) { // Api down?
    header("Location: ".$FallbackVideoLink);
    exit;
}

$videocount = count($data['items']);
$randomvideo = rand(0, intval($videocount)-1);
$videoID = $data['items'][$randomvideo]['id']['videoId'];

if(empty($videoID)) {
    header("Location: ".$FallbackVideoLink);
    exit;
} else {
    header("Location: http://youtube.com/watch?v=".$videoID);
    exit;
}
