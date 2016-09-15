<?php

/**
 * @link http://gurukami.com/
 * @copyright Copyright (c) 2016 Gurukami
 * @license MIT
 */

namespace Gurukami\Helpers;

/**
 * @package   Gurukami\Helpers
 * @author    Ilya Krasheninnikov <i.krasheninnikov@gurukami.com>
 */
class Arrays
{
    /**
     * Checks if the given key exists in the array by a string representation
     *
     * @param string $key <p>Name key in the array. Example: key[sub_key][sub_sub_key]</p>
     * @param array $array <p>The array. This array is passed by reference</p>
     *
     * @return bool returns true if key is exists, false otherwise
     */
    public static function exists($key, &$array)
    {
        if (!is_array($array)) {
            return false;
        }
        if ($key == '') {
            return isset($array[$key]);
        }
        $output = false;
        $cntEBrackets = 0;
        $splitStr = [];
        $isBroken = self::parseAndValidateKeys($key, $splitStr, $cntEBrackets);
        if ($isBroken || $cntEBrackets) {
            return false;
        }
        $evalStr = '[\'' . implode('\'][\'', $splitStr) . '\']';
        $prevEl = '';
        if (($strPosPrev = mb_strpos($evalStr, "']['")) !== false) {
            $prevEl = mb_substr($evalStr, 0, $strPosPrev + 2);
        }
        eval('$output=((isset($array' . $evalStr . ') && is_array($array' . $prevEl . ')))?true:false;');
        return $output;
    }

    /**
     * Save element to the array by a string representation
     *
     * @param string $key <p>Name key in the array. Example: key[sub_key][sub_sub_key]</p>
     * @param array $array <p>The array. This array is passed by reference</p>
     * @param mixed $value <p>The current value</p>
     *
     * @return bool returns true if success, false otherwise
     */
    public static function save($key, &$array, $value)
    {
        if (!is_array($array)) {
            return false;
        }
        if ($key == '') {
            $array[$key] = $value;
            return true;
        }
        if ($key === '[]') {
            $array[] = $value;
            return true;
        }
        $cntEBrackets = 0;
        $splitStr = [];
        $isBroken = self::parseAndValidateKeys($key, $splitStr, $cntEBrackets);
        if ($isBroken) {
            return false;
        }
        $append = preg_match('/\[\]$/', $key);
        if (($append && $cntEBrackets === 1) || (!$append && $cntEBrackets === 0)) {
            $evalStr = '';
            foreach ($splitStr as $el) {
                $return = false;
                $evalStr .= '[\'' . $el . '\']';
                eval('$return=isset($array' . $evalStr . ') && !is_array($array' . $evalStr . ');');
                if ($return) {
                    return false;
                }
            }
            eval(((!$append) ? 'unset($array' . $evalStr . ');' : '') . '$array' . $evalStr . (($append) ? '[]' : '') . '=$value;');
            return true;
        }
        return false;
    }

    /**
     * Delete element from the array by a string representation
     *
     * @param string $key <p>Name key in the array. Example: key[sub_key][sub_sub_key]</p>
     * @param array $array <p>The array. This array is passed by reference</p>
     *
     * @return bool returns true if success, false otherwise
     */
    public static function delete($key, &$array)
    {
        if (!is_array($array)) {
            return false;
        }
        if ($key == '') {
            unset($array[$key]);
            return true;
        }
        if ($key === '[]') {
            return false;
        }
        $cntEBrackets = 0;
        $splitStr = [];
        $isBroken = self::parseAndValidateKeys($key, $splitStr, $cntEBrackets);
        if ($isBroken || $cntEBrackets) {
            return false;
        }
        $evalStr = '[\'' . implode('\'][\'', $splitStr) . '\']';
        $prevEl = '';
        if (($strPosPrev = mb_strpos($evalStr, "']['")) !== false) {
            $prevEl = mb_substr($evalStr, 0, $strPosPrev + 2);
        }
        $output = false;
        eval('if((isset($array' . $evalStr . ') && is_array($array' . $prevEl . '))){unset($array' . $evalStr . ');$output=true;}else{$output=false;}');
        return $output;
    }

    /**
     * Get element of the array by a string representation
     *
     * @param string $key <p>Name key in the array. Example: key[sub_key][sub_sub_key]</p>
     * @param array $array <p>The array. This array is passed by reference</p>
     * @param string $default [optional] <p>Default value if key not exist, default: null</p>
     * @param bool $ignoreString [optional] <p>Ignore string element as array, get only element, default: true</p>
     *
     * @return mixed returns value by a key, or default value otherwise
     */
    public static function get($key, &$array, $default = null, $ignoreString = true)
    {
        if (!is_array($array)) {
            return $default;
        }
        if ($key == '') {
            if (!isset($array[$key])) {
                return $default;
            }
            return $array[$key];
        }
        if ($key === '[]') {
            return $default;
        }
        $output = $default;
        $cntEBrackets = 0;
        $splitStr = [];
        $isBroken = self::parseAndValidateKeys($key, $splitStr, $cntEBrackets);
        if ($isBroken || $cntEBrackets) {
            return $default;
        }
        $evalStr = '[\'' . implode('\'][\'', $splitStr) . '\']';
        $prevEl = '';
        if ($ignoreString) {
            if (($strPosPrev = mb_strpos($evalStr, "']['")) !== false) {
                $prevEl = mb_substr($evalStr, 0, $strPosPrev + 2);
            }
            eval('$output=(isset($array' . $evalStr . ') && is_array($array' . $prevEl . '))?$array' . $evalStr . ':$default;');
        } else {
            eval('$output=(isset($array' . $evalStr . ') && (is_array($array' . $prevEl . ') || is_string($array' . $prevEl . ')))?$array' . $evalStr . ':$default;');
        }
        return $output;
    }

    private static function parseAndValidateKeys($key, &$splitStr, &$cntEBrackets)
    {
        return preg_replace_callback(array('/(?J:\[([\'"])(?<el>.*?)\1\]|(?<el>\]?[^\[]+)|\[(?<el>(?:[^\[\]]+|(?R))*)\])/'),
            function ($m) use (&$splitStr, &$cntEBrackets) {
                if ($m[0] == '[]') {
                    $cntEBrackets++;
                    return '';
                }
                $splitStr[] = str_replace("'", "\\'", $m['el']);
                return '';
            }, $key);
    }
}
