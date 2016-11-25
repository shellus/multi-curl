<?php
/**
 * Created by PhpStorm.
 * User: shellus-out
 * Date: 2016/11/25
 * Time: 17:23
 */

namespace MultiCurl;


class Cookie
{
    /** @var CookieItem[] $items */
    protected $items = [];

    public function __construct($items)
    {
        $this -> items = $items;
    }


    static public function createByHeader($array){
        $items = [];
        foreach ($array as $item_str){
            $items[] = CookieItem::createByStr($item_str);
        }
        return new Cookie($items);
    }

}