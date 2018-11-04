<?php
set_time_limit(60*60*20); 
// ini_set('display_startup_errors', 1);
// ini_set('display_errors', 1);
// error_reporting(-1);

if(isset($_GET["url"]))
	$file = $_GET["url"];
else {
	header("HTTP/1.0 404 Not Found");
	exit;
}

$cooldown = 60 * 0.2;
$SESS_ID = md5($_SERVER['REMOTE_ADDR']);
$cachefile = "./.tmp/sess/mp3.data";

$cacheArray = [];
if(file_exists($cachefile))
	$cacheArray = unserialize(file_get_contents($cachefile));

$wTime = (time() - $cacheArray[$SESS_ID]['time_mp3']);

if(isset($cacheArray[$SESS_ID]) && isset($cacheArray[$SESS_ID]['time_mp3']) && ($wTime < $cooldown)) {
    if($_SERVER['REMOTE_ADDR'] != "127.0.0.1")
		exit("Wait ".($cooldown - $wTime)." sec");
}
else
	$cacheArray[$SESS_ID] = [];

$cacheArray[$SESS_ID]['time_mp3'] = time();

$typeA = (strpos($file, '.oga') !== false)?"ogg, audio/oga":"mpeg";

header('Content-type: audio/'.$typeA);
header("Content-Transfer-Encoding: binary");
header("Pragma: no-cache");

// Tor proxy service
if(isset($_GET["proxy"])) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:9050');
	curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
	curl_setopt($ch, CURLOPT_URL, $file);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300);
	$syn = curl_exec($ch);
	curl_close($ch);
	echo $syn;
	flush();
}
else {
	$fpOrigin = fopen($file, 'rb', false, stream_context_create([
		'http' => array(
			'method' => 'GET',
			'header' => "Accept-language: en\r\n"
		)
	]));

	while(!feof($fpOrigin)) {
		$buffer = fread($fpOrigin, 4096);
		echo $buffer;
		flush();
	}
	fclose($fpOrigin);
}


// Save session
file_put_contents($cachefile, serialize($cacheArray));
/*

$mime_type = "audio/mpeg, audio/x-mpeg, audio/x-mpeg-3, audio/mpeg3";
$filename = "mdewo_com_sound_get.mp3";

header("Content-type: $mime_type");
header("Content-length: ".filesize($file));
header("Content-Disposition: filename='".$filename."'");
header("X-Pad: avoid browser bug");
header("Cache-Control: no-cache");
readfile($file);
*/

?>