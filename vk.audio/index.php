<?php
set_time_limit(60*60*20); 

if(count($_GET)==0) {
	header("HTTP/1.0 404 Not Found");
	exit;
}

$cooldown = 60 * 0.2;
$SESS_ID = md5($_SERVER['REMOTE_ADDR']);
$cachefile = "../.tmp/sess/vk.audio.data";

$cacheArray = [];
if(file_exists($cachefile))
	$cacheArray = unserialize(file_get_contents($cachefile));

$wTime = (time() - $cacheArray[$SESS_ID]['time_mp3']);

if(isset($cacheArray[$SESS_ID]) && isset($cacheArray[$SESS_ID]['time']) && ($wTime < $cooldown)) {
	if($_SERVER['REMOTE_ADDR'] != "127.0.0.1") {
		echo json_encode(array(
			"error" => true,
			"message" => "Wait ".($cooldown - $wTime)." sec"
		));
		exit();
	}
}
else
	$cacheArray[$SESS_ID] = [];

$cacheArray[$SESS_ID]['time'] = time();

// Save session
file_put_contents($cachefile, serialize($cacheArray));

include "./.data/index.php";

?>