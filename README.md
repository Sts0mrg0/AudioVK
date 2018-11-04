# AudioVK

Использовалось как сайт на `PHP` типа API


## mp3.php - MP3 Proxy Telegram


Файл `mp3.php`
Использовался для воспроизведения аудио из Telegram 
Также некоторые проигрыватели на Linux не восспроизводили аудио VK по https и у VK была переадресация

#### Nginx
```nginx
	location /mp3 {
		try_files $uri $uri/ /mp3.php?$args;
	}
	location ~ /\. {
		deny all;
	}
```
Пример, формирования ссылок для воспроизведения музыки из Telegram
#### Запрос из бота клиенту
```js
// ...
request("http://localhost:9878/audio/play/url/?proxy=1&url="+data.fileLink, (err, resp, body) => {
	body = JSON.parse(body).status
	bot.sendMessage(msg.chat.id, "Start ["+msg.audio.title+" "+msg.audio.performer+"] OK\n["+body+"]", {
		replyMarkup: keyBoard("Volume")
	});
});	
```
#### Клиент. Плеер
```js
    // ...
app.get('/audio/play/url/', function(req, res) {
    var { url, proxy } = req.query,
        urls = "";
    if(proxy) {
    	urls = url.split(",").map(el => "http://127.0.0.1/mp3/?proxy=1&url="+el);
    }
	else {
		urls = url.split(",").map((el) => ( (el.startsWith("https"))?  ("http://127.0.0.1/mp3/?url="+el): el));
		urls = urls.filter((n)=> (n != undefined));
	}
	
	console.log(urls);
	
	player_music.play(urls, {url: true});
	
	res.json({
		"status": true
	});
});
```


### Воспроизведение из ВК
```js
vk.api.messages.getById({
	message_ids: message.id
})
.then((data)=> {
	data = data.items[0].attachments;
	var playAudioUrl = false,
    	listUrlPlay = "",
    	decodeUrl = "";

	if(data) {
		data.forEach(function(att) {
			if(att.type == "link" && att.link.url) {
				if(att.link.url.includes("audio_playlist")) {
					_.con("Try get data play pList");
					var playListID = false;
					var access_hash = false;

					var parss = att.link.url.match(/audio_playlist((-|)[0-9]+_[0-9]+)/i)
					if(parss !== null && parss[1] !== undefined) {
						playListID = parss[1];
					}
					if(!playListID)
						return _.con("izdec parse Get lPlist ID", true);

					parss = att.link.url.match(/&access_hash=([A-z0-9]+)(&|)/i)
					if(parss !== null && parss[1] !== undefined) {
						access_hash = parss[1];
					}

					request.post({
						url: 'http://127.0.0.1/vk.audio/?plist='+playListID+'&access_hash='+access_hash
					}, function (error, response, body) {
						if (error || response.statusCode != 200)
							return _.con("Error", true);

						if(body.length < 6)
							return _.con("Error get audio. short fck: \n"+body, true);
						body = JSON.parse(body)

						if(body.error)
							return console.log("Error api : ",body.message);
							
						var urls = [];
						body.audio.forEach(function (audio) {
							if(audio.src) {
								urls.push("http://127.0.0.1/mp3/?url="+audio.src);
							}
						});
						urls = urls.join(",");
						request("http://localhost:9878/audio/play/url/?url="+urls, (error, resp, body) => {
							if (!error && resp.statusCode == 200) {
								_.con("Send music cmd... Ok");
							}
							else
								console.log(error, body);
						});	
					});

				}
			}
			else if(att.type == "audio" && att.audio.url) {
				playAudioUrl = true;
				if(att.audio.url.includes("audio_api_unavailable") && att.audio.owner_id)
								// decodeUrl += att.audio.url + ",";
							listUrlPlay += vkDecode(att.audio.url, att.audio.owner_id) + ",";
							else
								listUrlPlay += att.audio.url + ",";
						}
					});

		if(playAudioUrl) {
			request("http://localhost:9878/audio/play/url/?url="+listUrlPlay, (error, resp, body) => {
				if (!error && resp.statusCode == 200) {
					_.con("Send music ulr cmd... Ok");
				}
				else
					console.log(error, body);
			});
		}
	}
});
```


