<?php
/*
*	Класс с функциями и автоавторизацией в Вк за меня... :(
*/

// $cookie = $_SERVER['DOCUMENT_ROOT'].'/avk/cookie.txt';
$cookie = $_SERVER['DOCUMENT_ROOT']."/vk.audio/.data/.cookie.txt";

$curl = curl_init();
$browser = 'Mozilla/5.0 (Windows NT 6.1; rv:26.0) Gecko/20100101 Firefox/26.0';

// $myID = 00; // в другом файле

/*function callback($buffer) {
	$buffer = preg_replace('/counter.yadro.ru/', '/', $buffer);
	$buffer = preg_replace('/scorecardresearch.com/', '/', $buffer);
	$buffer = preg_replace('/top-fwz1.mail.ru/', '/', $buffer);
	
	$buffer = preg_replace('/windows-1251/', 'utf-8', $buffer);
	$buffer = preg_replace('/vk.com/', 'vkproxy.mdewo.com', $buffer);
	$buffer = preg_replace('/m.vk.com/', 'vkproxy.mdewo.com', $buffer);
	$buffer = preg_replace('/href="\//', 'href="https://m.vk.com/', $buffer);
	$buffer = preg_replace('/src="\//', 'src="https://m.vk.com/', $buffer);
	return str_replace("яблоки", "апельсины", $buffer);
}

ob_start("callback");

// ...
ob_end_flush();*/



// Login process
$test = getx();
if (preg_match("/lg\_h\=(.*?)\&/is", $test) && !isset($_GET["code"]) && !isset($_GET["hash"]) ) {
	$resp = Login(array('email' => 'test@gmail.com', 'pass' => 'passr3d3'));
	
	if(preg_match("/authcheck_code/is", $resp)) {
		
		echo json_encode(array(
			"error" => true,
			"message" => "WEE NEED LOGIN VK CODE"
		));
		
		preg_match("/act\=authcheck\_code\&hash\=(.*?)\"/is", $resp, $re);
		?>

	<form action="" method="get" autocomplete="off" autocorrect="off" autocapitalize="off">
		<p><input type="hidden" name="hash" value="<?=$re[1]?>"></p>
		<p><input type="text" name="code" value="" placeholder="Code"></p>
		<p><input type="submit" value="Отправить код"></p>
	</form>	

<?php
	}
	
	echo json_encode(array(
		"error" => true,
		"message" => "we need Auto login"
	));
	exit;
}
else if(isset($_GET["code"]) && isset($_GET["hash"])) {
	$code = $_GET["code"];
	$hash = $_GET["hash"];
	
	$url = 'https://m.vk.com/login?act=authcheck_code&hash='.$hash;
	$data = array(
		"remember" => true,
		"code" => $code
	);
	$options = array(
		CURLOPT_URL				=> $url,
		CURLOPT_POST			=> 1,
		CURLOPT_POSTFIELDS		=> http_build_query($data),
		CURLOPT_COOKIEJAR		=> $cookie,
		CURLOPT_COOKIEFILE		=> $cookie,
		CURLOPT_RETURNTRANSFER	=> 1,
		//CURLOPT_HEADER		=> 1,
		CURLOPT_SSL_VERIFYPEER	=> 0,
		CURLOPT_SSL_VERIFYHOST	=> 0,
		CURLOPT_TIMEOUT			=> 30
	);
	$response = curlx($options);
	exit;
}



function LoginX($login, $pass) {
	$data = array(
		'email'	=> $login,
		'pass'	=> $pass
	);
	Login($data);
}
function Login($data) {
	global $cookie;
	$options = array (
		CURLOPT_COOKIEJAR	=> $cookie,
		CURLOPT_COOKIEFILE	=> $cookie,
		CURLOPT_URL			=> 'http://m.vk.com',
		CURLOPT_FOLLOWLOCATION	=> true,
		CURLOPT_RETURNTRANSFER	=> 1,
		CURLOPT_TIMEOUT		=> 30
	);

	$response = curlx($options);
		
	preg_match('/ip\_h\=(.*?)\&/is', $response, $match);
	$ip_h = $match[1];

	preg_match('/lg\_h\=(.*?)\&/is', $response, $match);
	$lg_h = $match[1];

	$url = 'https://login.vk.com/?act=login&_origin=https://m.vk.com&ip_h='.$ip_h.'&lg_h='.$lg_h.'&role=pda&utf8=1';
	
	$options = array(
		CURLOPT_URL				=> $url,
		CURLOPT_POST			=> 1,
		CURLOPT_POSTFIELDS		=> http_build_query($data),
		CURLOPT_COOKIEJAR		=> $cookie,
		CURLOPT_COOKIEFILE		=> $cookie,
		CURLOPT_RETURNTRANSFER	=> 1,
		//CURLOPT_HEADER		=> 1,
		CURLOPT_SSL_VERIFYPEER	=> 0,
		CURLOPT_SSL_VERIFYHOST	=> 0,
		CURLOPT_TIMEOUT			=> 30
	);	
	return curlx($options);
}

function getx($url = "https://m.vk.com", $data=null, $ref=false) {
	global $cookie;
	
	if(!$ref)$ref=$url;
	
	$options = array(
		CURLOPT_URL				=> $url,
		CURLOPT_COOKIEJAR		=> $cookie,
		CURLOPT_COOKIEFILE		=> $cookie,
		CURLOPT_RETURNTRANSFER	=> 1,
		CURLOPT_SSL_VERIFYPEER	=> 0,
		CURLOPT_SSL_VERIFYHOST	=> 0,
		CURLOPT_TIMEOUT			=> 30,
		CURLOPT_REFERER			=> $ref
	);	
	
	if(is_array($data)) {
		$options[CURLOPT_POST] = 1;
		$options[CURLOPT_POSTFIELDS] = http_build_query($data);
		
		if(isset($data["_ajax"])) {
			$options[CURLOPT_HTTPHEADER] = array(
				"Content-Type: application/x-www-form-urlencoded"
			);
		}
	}
	
	return curlx($options);
}
function curlx($options = null) {
	global $curl, $browser;
	
	$options[CURLOPT_USERAGENT] = $browser;
	curl_setopt_array($curl, $options);
	
	$exec = curl_exec($curl);
	
	return $exec;
}



// Decode vk mp3 link
function getSRC_U($hash) {
	global $myID;

	$extra = split("\?extra\=", $hash);
	if(count($extra) <= 0)
		return $hash;
	$extra = split("#", $extra[1]);
	$o = AZ($extra[1]);
	$hash = AZ($extra[0]);
	
	$o = split(chr(9), $o);
	$n = count($o);

	for(; $n-- ;) {
		$l = split(chr(11), $o[$n]);
		
		$s = array_splice($l, 0, 1, $hash)[0];
		
		if($s == "x") {
			$i = [];
			$chars = str_split($l[0]);
			foreach($chars as $char) {
				array_push($i, chr( ord($char) ^ ord($l[1]) ) );
			}
			$hash = join("", $i);
		}
		else if($s == "v") {
			$hash = strrev($l[0]);
		}
		else if($s == "r") {
			
			$tt = str_split($l[0]);
			
			$ch = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMN0PQRSTUVWXYZO123456789+/=";
			$a = count($tt);
			
			for(; $a--;) {
				$i = strrpos($ch, $tt[$a]);
				
				if($i > -1) {
					$tt[$a] = substr($ch, $i - $l[1], 1);
				}				
			}
			
			$hash = join("", $tt);
		}
		else if($s == "s") {
			$hash = SS($l[0], $l[1]);
		}
		else if($s == "i") {
			$hash = SS($l[0], $l[1] ^ $myID);
		}
		else
			return $hash;
	}
	
	return $hash;
}
function AZ($e) {
	if (!$e || strlen($e) % 4 == 1)
		return false;
	
	$ch = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMN0PQRSTUVWXYZO123456789+/=";
	$a = $o = 0;
	$r = "";
	
	for (; $o < strlen($e); ) {
		
		$i = strrpos($ch, substr($e, $o++, 1));
		if($i !== false) {
			
			$t = ($a % 4) ? (64 * $t + $i) : $i;
			if($a++ % 4) {
				$r .= chr(255 & $t >> (-2 * $a & 6));
			}
		}
	}
	
	return $r;
}
function SS($t, $e) {
    $i = strlen($t);
	
    if ($i > 0) {
		// 
        $o = [];
		if ($i > 0) {
			$a = $i;
			for ($e = abs($e); $a--;) {
				$e = ($i * ($a + 1) ^ $e + $a) % $i;
				$o[$a] = $e;
			}
		}
		//
		
        $a = 0;
		
        for ($t = str_split($t); ++$a < $i;)
			$t[$a] = array_splice($t, $o[$i - 1 - $a], 1, $t[$a])[0];
		
        $t = join("", $t);
    }
	
    return $t;
}

?>