<?php


class TestSuite extends \PHPUnit_Framework_TestCase
{
    public static function getStr()
    {
        return 'string';
    }

    public static function getInt()
    {
        return rand();
    }

    public static function getFloat()
    {
        return (float)rand();
    }

    public static function getRes()
    {
        static $res = '';
        if ($res) {
            return $res;
        }
        return $res = fopen("php://temp", "a");
    }

    public static function getClosure()
    {
        return function() {};
    }

    public static function getObj()
    {
        static $obj = '';
        if ($obj) {
            return $obj;
        }
        return $obj = (object)[];
    }
}