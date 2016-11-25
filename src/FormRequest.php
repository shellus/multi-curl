<?php
/**
 * Created by PhpStorm.
 * User: shellus-out
 * Date: 2016/11/25
 * Time: 14:31
 */

namespace MultiCurl;


class FormRequest extends Request
{
    public function __construct($url, array $body = [], array $headers = [])
    {
        $method = "POST";
        $headers['Content-Type'] = "application/x-www-form-urlencoded";
        parent::__construct($url, $method, http_build_query($body), $headers);
    }
}