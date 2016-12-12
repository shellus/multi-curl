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
use GuzzleHttp\Promise;

require __DIR__ . '/bootstrap.php';


// 处理接收到的数据
function handleResult(Response $response, $index)
{
    file_put_contents(__DIR__ . '/storage/' . $index . '.json', $response->getBody());
    var_dump($index);
}

// 处理接收到的数据
function handleRequestException(RequestException $e)
{
    var_dump( $e->getMessage());
    var_dump( $e->getRequest()->getMethod());
}

$client = new Client();

function build_request(){
    $date = $start_date = "20160221";
    $end_date = "20161101";
    while(strtotime($date) < strtotime($end_date)){

        $url = 'http://www.xjflcp.com/game/SelectDate';
        $headers = [
            "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8",
        ];
        $body = \GuzzleHttp\Psr7\build_query(['selectDate' => $date]);
        $request = new Request('POST', $url, $headers, $body);
        yield $date => $request;

        $timestamp = strtotime("{$date} +1 days");
        $date = date('Ymd', $timestamp);
    }
}


$pool = new \GuzzleHttp\Pool($client, build_request(),[
    'concurrency' => 30,
    'fulfilled' => 'handleResult',
    'rejected' => 'handleRequestException',
]);
$pool ->promise() -> wait();