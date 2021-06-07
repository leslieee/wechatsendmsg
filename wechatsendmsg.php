<?php

// 企业id
$id="wwfeb727e8c4f255bb";
// 应用id
$agentid="1000009";
// 应用secret
$secret="ysnn3k7WRHs05hzBtxhanaiFnB-O2lDZzpXibk-YYaa";
// 用户id [也就是用户账号，多个用户用|符号分开]
$touser="ZhangSan";

// 发送内容支持三种模式 cli fpm post/get
if (php_sapi_name() == "cli") {
    if ($argc == 2) {
        $text = $argv[1];
    } else {
        echo "php wechatsendmsg.php 发送内容\n";
        return;
    }
} else {
    if (!empty($_GET) && array_key_exists("text", $_GET)) {
        $text = $_GET["text"];
        if (array_key_exists("desp", $_GET) && !empty($_GET["desp"]) && strcmp($_GET["desp"], $_GET["text"]) != 0) {
            $text = $text . "\n" . $_GET["desp"];
        }
    } else if (!empty($_POST) && array_key_exists("text", $_POST)) {
        $text = $_POST["text"];
        if (array_key_exists("desp", $_POST) && !empty($_POST["desp"]) && strcmp($_POST["desp"], $_POST["text"]) != 0) {
            $text = $text . "\n" . $_POST["desp"];
        }
    } else {
        echo "接口调用格式错误\n";
        return;
    }
}

// API接口
$url="https://qyapi.weixin.qq.com/cgi-bin";

// 获取缓存token
$token = "";
if (file_exists("access_token")) {
    $file = file_get_contents("access_token");
    $file = json_decode($file, true);
    if ((time() - $file["time"]) < 3600) {
        $token = $file["access_token"];
    }
}
if (empty($token)) {
    // 缓存不存在或者过期则重新获取
    $token = file_get_contents("$url/gettoken?corpid=$id&corpsecret=$secret");
    $token = json_decode($token, true);
    if ($token["errcode"] == 0 && !empty($token["access_token"])) {
        $token = $token["access_token"];
        $file = array("access_token" => $token, "time" => time());
        file_put_contents("access_token", json_encode($file));
    } else {
        echo "获取token失败:" . $token["errmsg"] . "\n";
        return;
    }
}

$json = "{
	\"touser\": \"$touser\",
	\"msgtype\": \"text\",
	\"agentid\": \"$agentid\",
	\"text\": {
		\"content\": \"$text\"
	},
	\"safe\": \"0\"
}";

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "$url/message/send?access_token=$token",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $json,
    CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache",
        "Content-Type: application/json",
    ),
));
$response = curl_exec($curl);
$err = curl_error($curl);
if ($err) {
    $response = curl_exec($curl);
    $err = curl_error($curl);
    if ($err) {
        $response = curl_exec($curl);
        $err = curl_error($curl);
    }
}
echo $response . "\n";
curl_close($curl);