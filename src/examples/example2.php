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
require 'IssueBuild.php';




while (true)
{
    ob_start();


    $current = (new IssueBuild()) -> calcCurrentIssue(time());
    $previous = (new IssueBuild()) -> previous($current[0]);
    var_dump("当前可投注期数：{$current[0]}");

    if((new IssueBuild()) -> checkOpenStatus($previous[0])){
        var_dump("上期已开奖");
        $sleep = $current[1] - time();
    }else{
        var_dump("正在抓奖...");
        $url = 'http://www.xjflcp.com/game/SelectDate';
        $headers = [
            "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8",
        ];
        $body = \GuzzleHttp\Psr7\build_query(['selectDate' => date('Ymd')]);
        $request = new Request('POST', $url, $headers, $body);
        $data = (new Client) -> send($request);
        $opend = false;
        foreach (\GuzzleHttp\json_decode($data ->getBody(), true) as $item){
            if($item['lotteryIssue'] == $previous[0]){
                (new IssueBuild()) -> setOpenStatus($previous[0], $item['lotteryNumber']);
                $opend = true;
            }
        }
        if ($opend){
            var_dump("已经开奖: {$item['lotteryNumber']}");
            $sleep = $current[1] - time();
        }else{
            var_dump("没抓到奖");
            $sleep = 10;
        }
        var_dump("当前已过开奖时间期数：{$previous[0]}");
        printf("当前已过开奖时间：%s 秒" . PHP_EOL, time() - $previous[1]);
    }


    var_dump("睡觉: {$sleep} 秒");

    $s = ob_get_clean();
    echo mb_convert_encoding($s,'GBK');
    sleep($sleep);
}