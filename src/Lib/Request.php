<?php

namespace Lib;

class Request
{
    public static function get($foo, $return)
    {
        return self::valid($_REQUEST[$foo], $return);
    }
    
    public static function getFile($foo)
    {
        return $_FILES[$foo];
    }

    public static function getAll()
    {
        return array_merge($_POST, $_GET, $_FILES);
    }

    public static function uri()
    {
        return substr($_SERVER['REQUEST_URI'], 1);
    }

    /**
     * Which request method was used to access the page; i.e. 'GET', 'HEAD', 'POST', 'PUT'.
     *
     * @var $method string
     * @return Boolean
     */
    public static function isMethod($method)
    {
        return ($method == $_SERVER['REQUEST_METHOD']);
    }

    /**
     * return true/false if the variable exist on the request
     *
     * @var $value string
     * @return Boolean
     */
    public static function exist($value)
    {
        return isset($_REQUEST[$value]);
    }
    
    private static function valid(&$var, $return = false)
    {
        return !empty($var) ? $var : $return;
    }
}
