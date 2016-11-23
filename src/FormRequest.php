<?php namespace MultiCurl;
/**
 * Created by PhpStorm.
 * User: shellus-out
 * Date: 2016/11/23
 * Time: 9:33
 */



class FormRequest extends Request
{

    protected $method = 'POST';

    /**
     * FormRequest constructor.
     * @param string $url
     * @param array $data
     */
    public function __construct($url, array $data)
    {
        parent::__construct($url, http_build_query($data));
        $this -> headers['Content-Type'] = 'application/x-www-form-urlencoded';
    }
}