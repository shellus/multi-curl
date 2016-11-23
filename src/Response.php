<?php
/**
 * Created by PhpStorm.
 * User: shellus-out
 * Date: 2016/11/23
 * Time: 9:46
 */

namespace MultiCurl;


class Response
{
    protected $contentType = 'text/html';
    protected $content = '';
    /** @var array $data  */
    protected $data = [];

    public function __construct($content)
    {
        $this -> content = $content;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    public function saveToFile($file){
        return file_put_contents($file, $this -> getContent());
    }


    public function __toString()
    {
        return $this -> content;
    }

}