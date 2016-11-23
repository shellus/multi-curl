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

    $request['id'] = $i;
    $request -> setClosure(function (\MultiCurl\JsonResponse $response, $request){
        var_dump('id: ' . $request['id'], 'content: ' . substr($response -> getContent(),0, 200));
    });

    return $request;
}


function getstackoverflow($i){
    $url = 'http://stackoverflow.com/questions/1225409/how-to-switch-from-post-to-get-in-php-curl';

    $request = new MultiCurl\Request($url);

    $request['id'] = $i;
    $request -> setClosure(function (\MultiCurl\Response $response, $request){
        var_dump('id: ' . $request['id'], 'content: ' . substr($response -> getContent(),0, 200));
    });

    return $request;
}


for($i =0; $i < 10; $i++){
    $m -> addRequest(postxjflcp($i));
    $m -> addRequest(getstackoverflow($i));
}
$m -> exec();