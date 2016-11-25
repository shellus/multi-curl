# Multi-curl
PHP异步CURL，轻易的在PHP中进行并发的http请求，可用于针对复杂接口的压力测试

### 特点
 - 自动的编码解码，使用`FormRequest`来自动编码form表单提交，使用JsonResponse来自动解码返回的json数据
 - 和其他压力测试工具不同，本公举支持POST、请求构造等特性。

### 安装

```bash
composer require shellus/multi-curl
```

### 示例代码

```php

/**
 * 单请求多并发示例
 * User: shellus-out
 * Date: 2016/11/25
 * Time: 10:16
 */

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



```
更多例子请查阅`examples`文件夹

### 本项目参考了以下页面
 - php curl文档 ： http://php.net/manual/en/function.curl-multi-init.php