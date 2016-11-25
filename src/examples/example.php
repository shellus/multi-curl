<?php
/**
 * 单请求多并发示例
 * User: shellus-out
 * Date: 2016/11/25
 * Time: 10:16
 */

require __DIR__ . '/bootstrap.php';
// 处理接收到的数据
function handleResult(\MultiCurl\Response $response){
    $info = $response -> getInfo();


    $text = substr($response -> getContent(), 0, 200);
    $text = str_replace("\r\n", "\n", $text);
    $text = str_replace("\n", "", $text);

    var_dump('url: ' . $info['url'], 'content: ' . $text);
}

// 并发50
$m = new \MultiCurl\MultiCurl(50);

for($i =0; $i < 10; $i++){
    $url = 'http://www.xjflcp.com/game/SelectDate';
    $data = ['selectDate'=>'20161122'];
    $request = new MultiCurl\FormRequest($url, $data);
    $request -> setClosure('handleResult');
    $m -> pushRequest($request);
}

$m -> exec();