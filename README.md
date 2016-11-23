# Multi-curl
PHP异步CURL，轻易的在PHP中进行并发的http请求

### 特点
 - 自动的编码解码，使用`FormRequest`来自动编码form表单提交，使用JsonResponse来自动解码返回的json数据
 - 


### 示例代码

```php

// 处理接收到的数据
function handleResult(\MultiCurl\Response $response){
    $info = $response -> getInfo();


    $text = substr($response -> getContent(), 0, 200);
    $text = str_replace("\r\n", "\n", $text);
    $text = str_replace("\n", "", $text);
    
    var_dump('url: ' . $info['url'], 'content: ' . $text);
}


$m = new \MultiCurl\MultiCurl();

for($i =0; $i < 10; $i++){
    $url = 'http://www.xjflcp.com/game/SelectDate';
    $data = ['selectDate'=>'20161122'];
    $request = new MultiCurl\FormRequest($url, $data);
    $request -> setClosure('handleResult');
    $m -> addRequest($request);
}

$m -> exec();



```
更多例子请查阅`examples`文件夹

### 本项目参考了以下页面
 - php curl文档 ： http://php.net/manual/en/function.curl-multi-init.php