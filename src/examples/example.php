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


abstract class RequestPool
{
    /** @var Client */
    protected $client;
    /** @var \GuzzleHttp\Pool */
    protected $pool;

    public function __construct()
    {
        $this -> client = new Client();
    }

    /**
     * 生成请求
     * @return Generator
     */
    abstract public function build_request();

    /**
     * 处理接收到的数据
     * @param Response $response
     * @param $index
     */
    abstract public function handleResult(Response $response, $index);

    /**
     * 处理请求错误
     * @param RequestException $e
     */
    public function handleRequestException(RequestException $e)
    {
        var_dump($e->getMessage());
        var_dump($e->getRequest()->getMethod());
    }

    public function run(){
        $this -> pool = new \GuzzleHttp\Pool($this -> client, $this -> build_request(), [
            'concurrency' => 30,
            'fulfilled' => array($this, 'handleResult'),
            'rejected' => array($this, 'handleRequestException'),
        ]);
        $this -> pool->promise()->wait();
    }
}


class XJSSCRequestPool extends RequestPool
{

    /**
     * 生成请求
     * @return Generator
     */
    public function build_request()
    {
        $date = $start_date = "20160221";
        $end_date = "20161101";
        while (strtotime($date) < strtotime($end_date)) {

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
    public function storage($data, $index){
        file_put_contents(__DIR__ . '/storage/' . $index . '.json', $data);
        var_dump($index);
    }

    /**
     * 处理接收到的数据
     * @param Response $response
     * @param $index
     */
    public function handleResult(Response $response, $index)
    {
        $data = $response->getBody() -> __toString();


        $this -> storage(json_encode(json_decode($data), JSON_PRETTY_PRINT), $index);
    }
}


(new XJSSCRequestPool()) -> run();


