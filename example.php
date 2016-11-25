<?php
/**
 * Created by PhpStorm.
 * User: shellus-out
 * Date: 2016/11/25
 * Time: 10:51
 */

require __DIR__ . '/vendor/autoload.php';

$url = 'http://blog.endaosi.com/index.php/action/login?_=f2120c9d16a7cf9af721e0fa59f318f9';
$from = [
    'name' => 'shellus',
    'password' => 'a7245810',
    'referer' => 'http://blog.endaosi.com/admin/',
];
$req = new MultiCurl\FormRequest($url, $from);

echo $req -> sendWithCurl() ->getBody();
