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
    /** @var Request[] $requests */
    protected $requests = [];

    /** @var \SplQueue $queue */
    protected $queue;

    /** @var int $maxConcurrency 最大并发数 */
    protected $maxConcurrency = 0;

    public function __construct($maxConcurrency = 100)
    {
        $this -> maxConcurrency = $maxConcurrency;
        $this -> queue = new \SplQueue();
        $this->master = curl_multi_init();
    }

    public function __destruct()
    {

        foreach ($this->requests as $item) {
            curl_multi_remove_handle($this->master, $item->getHandle());
        }
        curl_multi_close($this->master);
    }

    public function pushRequest(Request $request)
    {
        $this -> queue -> push($request);
        return true;
    }

    public function joinRequest(Request $request){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request->getUrl()); // set url

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request->getMethod()); // set method
        if ($body_data = $request -> getBody()){
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body_data); // request body
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $h = $request->getHeadersForCurl());
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip'); // 自适应gzip
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 不直接输出
        curl_multi_add_handle($this->master, $ch);

        $request->setHandle($ch);
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
                $this -> joinRequest($this -> queue -> pop());
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

            /*
            // TODO 进度输出
            foreach ($this->items as $request) {
                $l = curl_getinfo($request->getHandle(), CURLINFO_CONTENT_LENGTH_DOWNLOAD);
                if ($l != -1) {
                    $p = curl_getinfo($request->getHandle(), CURLINFO_SIZE_DOWNLOAD);
                    var_dump($p . '/' . $l . ': ' . round($p / $l * 100, 2) . '%');
                }
            }
            */

            // 如果上面的信号有了，那这里就要一直拿数据。因为可能有1-N个请求响应完成
            while ($info = curl_multi_info_read($mh)) {
                if ($info["result"] == CURLE_OK) {
                    foreach ($this->requests as $key => $request) {
                        if ($request->getHandle() === $info['handle']) {
                            unset($this -> requests[$key]);

                            if($this -> queue -> count() !== 0){
                                $this -> joinRequest($this -> queue -> pop());
                            }
                            $this -> callanle($request);
                            curl_multi_remove_handle($this -> master, $request -> getHandle());

                            break;
                        }
                    }
                } else {
                    throw new \Exception(curl_error($info['handle']));
                }
            }


        }
        return true;
    }


    public function callanle(Request $request){
        $reflector = new \ReflectionFunction($request -> getClosure());
        $parameters = $reflector->getParameters();
        $parameter = $parameters[0];

        if($arr = $parameter->getClass()){
            switch ($arr -> name){
                case JsonResponse::class:
                    $responseClass = JsonResponse::class;
                    break;
                default:
                    $responseClass = Response::class;
                    break;
            }


            $response = new $responseClass(curl_multi_getcontent($request -> getHandle()));

            /** @var Response $response */
            $response -> setInfo(curl_getinfo($request->getHandle()));
            $response -> setRequest($request);

            call_user_func($request -> getClosure(), $response);
        }

    }
}