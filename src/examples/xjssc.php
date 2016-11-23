<?php
/**
 * Created by PhpStorm.
 * User: shellus-out
 * Date: 2016/11/23
 * Time: 9:28
 */

require __DIR__ . '/../../vendor/autoload.php';
$m = new \MultiCurl\MultiCurl();

function postxjflcp($i){
    $url = 'http://www.xjflcp.com/game/SelectDate';
    $data = ['selectDate'=>'20161122'];
    $request = new MultiCurl\FormRequest($url, $data);
    $request -> setClosure('handleResult');
    return $request;
}


function getstackoverflow($i){
    $url = 'http://stackoverflow.com/questions/1225409/how-to-switch-from-post-to-get-in-php-curl';
    $request = new MultiCurl\Request($url);
    $request -> setClosure('handleResult');
    return $request;
}

function handleResult(\MultiCurl\Response $response){
    $info = $response -> getInfo();


    $text = substr($response -> getContent(), 0, 200);
    $text = str_replace("\r\n", "\n", $text);
    $text = str_replace("\n", "", $text);


    var_dump('url: ' . $info['url'], 'content: ' . $text);
}


for($i =0; $i < 10; $i++){
    $m -> addRequest(postxjflcp($i));
    $m -> addRequest(getstackoverflow($i));
}
$m -> exec();