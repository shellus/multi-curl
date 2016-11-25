<?php
/**
 * Created by PhpStorm.
 * User: shellus-out
 * Date: 2016/11/25
 * Time: 14:00
 */

namespace MultiCurl;


class Request
{
    protected $url = '';
    protected $method = 'GET';
    protected $body = '';
    protected $defaultHeaders = [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36',
        'Accept-Language' => 'zh-CN,zh;q=0.8',
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Connection' => 'keep-alive',
    ];
    protected $headers = [];
    protected $tmp_path = __DIR__;

    public function __construct($url, $method = 'GET', $body = '', $headers = [])
    {
        $this -> method = $method;
        $this -> url = $url;
        $this -> body = $body;
        $this -> headers = array_merge($this -> defaultHeaders, $headers);


    }

    /**
     * @param $cookie_file
     * @return resource
     */
    private function createCh()
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FORBID_REUSE, false); // 长连接
        curl_setopt($ch, CURLOPT_URL, $this -> url);
        // 这个坑爹的，竟然遇到302跳转还是去POST跳转
//        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this -> method);
        if($this -> method == "POST"){
            curl_setopt($ch, CURLOPT_POST, 1);
        }else{
            curl_setopt($ch, CURLOPT_POST, 0);
        }
        if ($this -> body){
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this -> body);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $h = $this->getHeadersForCurl());

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 跟随跳转
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip'); // 自适应gzip
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 不直接输出
        curl_setopt($ch, CURLOPT_HEADER, 1); // 接收headers

        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        return $ch;
    }

    private function closeCh($ch){
        curl_close($ch);
    }

    public function getHeadersForCurl()
    {
        return array_map(function($a,$b){
            return $a . ': ' . $b;
        },array_keys($this->headers), $this->headers);
    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function sendWithCurl(){


        $ch = $this -> createCh();
        $body = curl_exec($ch);

        if(curl_errno($ch)){
            throw new \Exception(curl_error($ch));
        }
        $response = Response::createByCurlHandle($ch, $body);

        $this -> closeCh($ch);
        return $response;
    }

    /**
     * @param MultiCurl $multiCurl
     * @param $callback
     * @param null $sign
     * @throws \Exception
     */
    public function sendWithMultiCurl(MultiCurl $multiCurl, $callback, $sign = null){
        $ch = $this -> createCh();
        $multiCurl -> join($ch, function($ch, $sign)use($callback){
            $response = Response::createByCurlHandle($ch, curl_multi_getcontent($ch));
            $this -> closeCh($ch);
            call_user_func($callback, $response, $sign);
        }, $sign);
    }
}