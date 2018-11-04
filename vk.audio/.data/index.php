<?php
// ini_set('display_errors', 'on');

// ID страницы, через которую грузим аудио
$myID = 142679294;

if(isset($_GET["id"]) && strlen($_GET["id"]) > 3) {
	$ids = htmlspecialchars($_GET["id"]);
	$ids = preg_replace("/[^0-9_,\-]/", "", $ids);
}

$accesHash = (isset($_GET["access_hash"]) && strlen($_GET["access_hash"]) > 8)?
	("&access_hash=".preg_replace('![^\w\d]*!', '', $_GET["access_hash"])): "";

$search = $ids = $plist = false;
// Поиск по названию
if(isset($_GET["q"]) && strlen($_GET["q"]) < 45) {
	$search = htmlspecialchars($_GET["q"]);
	$search = preg_replace('/[^0-9А-яA-zЁё -_]/ui', '', $search);
}
// Получение по ID аудио
else if(isset($_GET["id"]) && strlen($_GET["id"]) > 3) {
	$ids = htmlspecialchars($_GET["id"]);
	$ids = preg_replace("/[^0-9_,\-]/", "", $ids);
}
// Получение по ID альбома
else if(isset($_GET["plist"]) && strlen($_GET["plist"]) > 4) {
	$plist = htmlspecialchars($_GET["plist"]);
	$plist = preg_replace("/[^0-9_,\-]/", "", $plist);
}
// Для авторизации
else if(!isset($_GET["code"]) || !isset($_GET["hash"])) {
	echo json_encode(array(
		"error" => true,
		"message" => "Неверный запрос"
	));
	exit();
}

include "vau.class.php";

if($ids) {
	$result = false;
	$audiosArray = [];
	$countAudios = 0;

	$ids = explode(",", $ids);
	$ids = array_chunk($ids, 10);

	foreach ($ids as $key => $adi) {
		$adi = implode(",", $adi);
		if($key > 1) sleep(1);
		if($key > 4) break;

		$responseJSON = getx("https://vk.com/al_audio.php?act=reload_audio&al=1&ids=".$adi.$accesHash);
		$responseJSON = iconv('windows-1251', 'UTF-8', $responseJSON);
		var_dump($responseJSON);exit();
		
		if(strpos($responseJSON, "<!json>") !== false) {
			
			$responseJSON = preg_replace("/^<!--/", '', $responseJSON);
			$responseJSON = preg_replace('/-<>-(!?)>/', '--$1>', $responseJSON);
			$responseJSON = explode("<!>", $responseJSON);
			
			if(count($responseJSON) >= 5) {
				$responseJSON = $responseJSON[5];
				if($responseJSON == "<!bool>") {
					echo json_encode(array("error" => true, "message" => "Need access_hash"));
					exit();
				}
				$responseJSON = explode("<!json>", $responseJSON)[1];
				$responseJSON = json_decode($responseJSON);
				
				
				if(count($responseJSON) >= 1) {
					
					foreach($responseJSON as $audioA) {
						
						if(count($audioA) < 2)
							break;
						
						$url = $audioA[2];
						$name1 = $audioA[3];
						$name2 = $audioA[4];
						
						$url = getSRC_U($url);
						$url = split("\?extra\=", $url)[0];
						
				    	// $name1 = preg_replace('![^\w\d\s]*!', '', $name1);
			    		// $name2 = preg_replace('![^\w\d\s]*!', '', $name2);
						
				    	$name1 = preg_replace('/[^0-9А-яA-zЁё -_]/ui', "", $name1);
			    		$name2 = preg_replace('/[^0-9А-яA-zЁё -_]/ui', '', $name2);
						
						$newAUDIO = array(
							"src" => $url,
							"name1" => $name1,
							"name2" => $name2
						);
						
						array_push($audiosArray, $newAUDIO);
						$countAudios++;
					}
					// echo '<audio preload="metadata" controls="controls" src="'.$url.'" >err code</audio>'."<br>\n";
				}
			}
		}

	}

	if(count($audiosArray) > 0) {
		echo json_encode(array("count" => $countAudios, "audio" => $audiosArray));
		exit();
	}
	
	echo json_encode(array("error" => true, "message" => "Not found sound"));
	exit();
}
else if($plist) {

	$result = false;
	$audiosArray = [];
	$countAudios = 0;

	$plist = explode("_", $plist);

	$responseJSON = getx("https://vk.com/al_audio.php?act=load_section&type=playlist&al=1&owner_id={$plist[0]}&playlist_id={$plist[1]}".$accesHash);
	$responseJSON = iconv('windows-1251', 'UTF-8', $responseJSON);
	
	if(strpos($responseJSON, "<!json>") !== false) {
			
		// var_dump($responseJSON);
		$responseJSON = preg_replace("/^<!--/", '', $responseJSON);
		$responseJSON = preg_replace('/-<>-(!?)>/', '--$1>', $responseJSON);
		$responseJSON = explode("<!>", $responseJSON);
			
		if(count($responseJSON) >= 5) {
			$responseJSON = $responseJSON[5];
			if($responseJSON == "<!bool>") {
				echo json_encode(array("error" => true, "message" => ($accesHash=="")?"Need access_hash":"ZZH Error get plist"));
				exit();
			}

			$responseJSON = explode("<!json>", $responseJSON)[1];
			$responseJSON = json_decode($responseJSON);

			if($responseJSON->type == "playlist" && count($responseJSON->list) >= 1 ) {

				foreach($responseJSON->list as $audioA) {

					if(count($audioA) < 2)
						break;

					$url = $audioA[2];
					$name1 = $audioA[3];
					$name2 = $audioA[4];

					$url = getSRC_U($url);
					$url = split("\?extra\=", $url)[0];

				    // $name1 = preg_replace('![^\w\d\s]*!', '', $name1);
			    	// $name2 = preg_replace('![^\w\d\s]*!', '', $name2);

					$name1 = preg_replace('/[^0-9А-яA-zЁё -_]/ui', "", $name1);
					$name2 = preg_replace('/[^0-9А-яA-zЁё -_]/ui', '', $name2);

					$newAUDIO = array(
						"src" => $url,
						"name1" => $name1,
						"name2" => $name2
					);

					array_push($audiosArray, $newAUDIO);
					$countAudios++;
				}
					// echo '<audio preload="metadata" controls="controls" src="'.$url.'" >err code</audio>'."<br>\n";
			}

		}
	}

	if(count($audiosArray) > 0) {
		echo json_encode(array("count" => $countAudios, "audio" => $audiosArray));
		exit();
	}
	
	echo json_encode(array("error" => true, "message" => "Not found sound"));
	exit();
}
else if($search) {
	$response = getx("https://m.vk.com/audio?act=search&q=".urlencode($search), ["_ajax" => 1], "https://m.vk.com/audio?act=popular");

	if(preg_match('/audio\_api\_unavailable\.mp3(.*?)">/', $response, $match)) {
		preg_match_all('/audio\_api\_unavailable\.mp3(.*?)">/', $response, $match);
		
		$audiosArray = [];
		$countAudios = 0;
		
		foreach($match[1] as $url) {
			$url = getSRC_U($url);
				
			$newAUDIO = array(
				"src" => $url
			);
			
			array_push($audiosArray, $newAUDIO);
			$countAudios++;
			
			// echo '<audio preload="metadata" controls="controls" src="'.$url.'" >err code</audio>'."<br>\n";
		}
		echo json_encode(array("count" => $countAudios, "audio" => $audiosArray));
		exit();
	}
	echo json_encode(array("error" => true, "message" => "Not found sound"));
	exit();
}


/*$dom = new DOMDocument();
$dom->loadHTML($response);
$xpath = new DOMXPath($dom);

// getSRC("545453421231_123123_audios545453421231");

function getSRC($e) {
	global $xpath;
	$result = false;
	foreach($xpath->evaluate('//div[@id="'. $e .'"]//input/@value') as $nn) {
		$result = $nn->value;
	}
	return $result;
}

if(preg_match('/<div id\=\"(audio.*?)\"(.*?)\)\">/', $response, $match)) {
	preg_match_all('/<div id\=\"(audio.*?)\"(.*?)\)\">/', $response, $match);
	
	foreach($match[1] as $url) {
		$a = getSRC($url);
		var_dump($a);
	}
}*/



?>