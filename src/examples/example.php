<?php
/**
 * 单请求多并发示例
 * User: shellus-out
 * Date: 2016/11/25
 * Time: 10:16
 */


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

require __DIR__ . '/bootstrap.php';


// 处理接收到的数据
function handleResult(Response $response)
{
    $text = substr($response->getBody(), 0, 200);
    $text = str_replace("\r\n", "\n", $text);
    $text = str_replace("\n", "", $text);
    var_dump("status: {$response -> getStatusCode()}  content: $text");
}

// 并发50
$client = new Client();

for ($i = 0; $i < 10; $i++) {

    $url = 'http://www.xjflcp.com/game/SelectDate';
    $body = \GuzzleHttp\Psr7\build_query(['selectDate' => '20161122']);
    $headers = [
        "Host" => "www.xjflcp.com",
        "Connection" => "keep-alive",
        "Accept" => "text/plain, */*; q=0.01",
        "Origin" => "http://www.xjflcp.com",
        "X-Requested-With" => "XMLHttpRequest",
        "User-Agent" => "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36",
        "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8",
        "Referer" => "http://www.xjflcp.com/game/sscAnnounce",
        "Accept-Encoding" => "gzip, deflate",
        "Accept-Language" => "zh-CN,zh;q=0.8",
    ];
    $request = new Request('POST', $url, $headers, $body);

    $promise = $client->sendAsync($request);
}

$promise->then(
    function ($response) {
        handleResult($response);
    },
    function (RequestException $e) {
        echo $e->getMessage() . "\n";
        echo $e->getRequest()->getMethod();
    });
$promise->wait();
