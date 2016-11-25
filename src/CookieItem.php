<?php
/**
 * Created by PhpStorm.
 * User: shellus-out
 * Date: 2016/11/25
 * Time: 17:46
 */

namespace MultiCurl;


class CookieItem
{
    protected $name;
    protected $value;
    protected $minutes;
    protected $path;
    protected $domain;
    protected $secure;
    protected $httpOnly;

    public function __construct($name = null, $value = null, $minutes = 0, $path = null, $domain = null, $secure = false, $httpOnly = true)
    {
        $this->name = $name;
        $this->value = $value;
        $this->minutes = $minutes;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
    }

    static public function createByStr($str)
    {
        $items = explode('; ', $str);

        $name = null;
        $value = null;
        $minutes = 0;
        $path = null;
        $domain = null;
        $secure = false;
        $httpOnly = true;

        foreach ($items as $index => $item_str) {


            $gap = strpos($item_str, '=');
            if ($gap === false){
                if ($item_str === "ecure"){
                    $secure = true;
                }
                continue;
            }

            $first = substr($item_str, 0, $gap);
            $last = substr($item_str, $gap + 1);

            if($index === 0){
                $name = $first;
                $value = $last;
                continue;
            }

            switch ($first){
                case "expires":
                    // 已经被Max-Age取代
//                    $minutes = $value;
                    break;
                case "Max-Age":
                    $minutes = $value;
                    break;
                case "path":
                    $path = $value;
                    break;
                case "domain":
                    $domain = $value;
                    break;
            }
        }
        return new CookieItem($name, $value, $minutes, $path, $domain, $secure, $httpOnly);
    }
}