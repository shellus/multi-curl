<?php
/**
 * Created by PhpStorm.
 * User: shellus-out
 * Date: 2016/11/25
 * Time: 10:51
 */

require __DIR__ . '/vendor/autoload.php';

$url = 'http://127.0.0.1/test.php';
$from = [
    'name' => 'shellus@endaosi.com',
    'password' => 'a7245810',
    'referer' => 'http://blog.endaosi.com/admin/',
];
$headers = [
    'Host' => 'shuoxingba.localhost'
];

$req = new MultiCurl\Request($url, 'GET','', $headers);


// 同步
//echo $req -> sendWithCurl() ->getBody();

// 异步
$m = new \MultiCurl\MultiCurl();
$req -> sendWithMultiCurl($m, function(\MultiCurl\Response $response){
    echo $response -> getBody();
});
$m -> exec();


