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
    protected $requests = [];
    /** @var \SplQueue $queue */
    protected $queue;

    /** @var int $maxConcurrency 最大并发数 */
    protected $maxConcurrency = 100;

    protected $callback;

    public function __construct($maxConcurrency = 100)
    {
        $this -> maxConcurrency = $maxConcurrency;
        $this -> queue = new \SplQueue();
        $this->master = curl_multi_init();
    }

    public function __destruct()
    {
        foreach ($this->requests as $item) {
            curl_multi_remove_handle($this->master, $item);
        }
        curl_multi_close($this->master);
    }

    public function push($ch)
    {
        $this -> queue -> push($ch);
        return true;
    }

    public function join($request){
        curl_multi_add_handle($this->master, $request);
        $this -> requests[] = $request;
        return true;
    }


    /**
     * @return bool
     * @throws \Exception
     */
    public function exec()
    {

        for ($i=0;$i<$this -> maxConcurrency;$i++){
            if($this -> queue -> count() !== 0){
                $this -> join($this -> queue -> pop());
            }
        }
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
                    foreach ($this->requests as $key => $ch) {
                        if ($ch === $info['handle']) {
                            unset($this -> requests[$key]);

                            if($this -> queue -> count() !== 0){
                                $this -> join($this -> queue -> pop());
                            }
                            call_user_func($this->callback, $ch);
                            curl_multi_remove_handle($this -> master, $ch);
                            curl_close($ch);
                            break;
                        }
                    }
                } else {

//                    throw new \Exception(curl_error($info['handle']));
                    var_dump(curl_error($info['handle']));
                }
            }
        }
        return true;
    }


    public function temp($ch){
        curl_getinfo($ch);
        curl_multi_getcontent($ch);
    }

    /**
     * @param mixed $callback
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }
}