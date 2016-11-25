<?php
/**
 * Created by PhpStorm.
 * User: shellus-out
 * Date: 2016/11/25
 * Time: 14:17
 */

namespace MultiCurl;


class Response
{
    protected $statusCode;
    protected $url;
    protected $headers;
    protected $body;
    /** @var  Cookie $cookie */
    protected $cookie;

    public function __construct($url, $body = '', $headers = [], Cookie $cookie, $statusCode = 200)
    {
        $this -> statusCode = $statusCode;
        $this -> url = $url;
        $this -> headers = $headers;
        $this -> body = $body;
        $this -> cookie = $cookie;
    }

    static public function createByCurlHandle($ch, $response){

        $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header_str = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        $header_str = @end(explode("\r\n\r\n", trim($header_str)));

//        file_put_contents($this -> tmp_path . '/header_str.html', $header_str);
//        file_put_contents($this -> tmp_path . '/body.html', $body);
//        var_dump(curl_getinfo($ch, CURLINFO_HEADER_OUT));

        $headers = [];
        $statusCode = 200;

        foreach (explode("\r\n", trim($header_str)) as $i => $line){
            if($i === 0){
                list($httpVersion, $statusCode, $statusStr) = explode(' ', $line);

            }else{
                $key = substr($line, 0, $gap = strpos($line, ": "));
                $value = substr($line, $gap+2);

                if (in_array($key,['Set-Cookie'])){
                    $headers[$key][] = $value;
                }else{
                    $headers[$key] = $value;
                }
            }
        }
        return new Response($url, $body, $headers, Cookie::createByHeader($headers['Set-Cookie']), $statusCode);
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return Cookie
     */
    public function getCookie()
    {
        return $this->cookie;
    }

}