<?php namespace MultiCurl;

/**
 * Created by PhpStorm.
 * User: shellus-out
 * Date: 2016/11/22
 * Time: 16:27
 */
class MultiCurl
{
    protected $master;
    protected $curlHandles = [];


    public function __construct()
    {
        $this->master = curl_multi_init();
    }

    public function __destruct()
    {
        foreach ($this->curlHandles as $key => $value) {
            list($ch, $callback, $sign) = $value;
            curl_multi_remove_handle($this->master, $ch);
        }
        curl_multi_close($this->master);
    }

    public function join($ch, $callback, $sign = null)
    {
        curl_multi_add_handle($this->master, $ch);
        $this -> curlHandles[] = [$ch, $callback, $sign];
        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function exec()
    {

        /** @var resource $mh curl句柄 */
        $mh = $this->master;

        /** @var int $still_running 仍然运行中的数量 */
        $still_running = 0;

        do {
            // 开始处理CURL上的子连接
            $mrc = curl_multi_exec($mh, $still_running);

        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($still_running && $mrc == CURLM_OK) {

            // 等待socket信号
            if (curl_multi_select($mh) == -1) {
                usleep(100);
            }

            do {
                // 开始处理CURL上的子连接
                $mrc = curl_multi_exec($mh, $still_running);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);


            // 如果上面的信号有了，那这里就要一直拿数据。因为可能有1-N个请求响应完成
            while ($info = curl_multi_info_read($mh)) {
                if ($info["result"] == CURLE_OK) {
                    $this -> complete($info);
                } else {
//                    throw new \Exception(curl_error($info['handle']));
                    var_dump(curl_error($info['handle']));
                }
            }
        }
        return true;
    }

    private function complete($info){
        foreach ($this->curlHandles as $key => $value) {
            list($ch, $callback, $sign) = $value;
            if ($ch === $info['handle']) {
                unset($this -> curlHandles[$key]);
                call_user_func($callback, $ch, $sign);
                curl_multi_remove_handle($this -> master, $ch);
                break;
            }
        }
    }
}