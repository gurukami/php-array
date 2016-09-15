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
     * @param array $array <p>The array</p>
     *
     * @return bool returns true if key is exists, false otherwise
     */
    public static function exists($key, $array)
    {
        if (!is_array($array)) {
            return false;
        }
        if ($key == '') {
            return is_array($array) && array_key_exists((string)$key, $array);
        }
        $parseInfo = self::parseAndValidateKeys($key);
        if ($parseInfo['isBroken'] || $parseInfo['cntEBrackets']) {
            return false;
        }
        return eval('return (is_array($array' . $parseInfo['prevEl'] . ') && array_key_exists(\'' . $parseInfo['endKey'] . '\',$array' . $parseInfo['prevEl'] . '))?true:false;');
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
        $parseInfo = self::parseAndValidateKeys($key);
        if ($parseInfo['isBroken']) {
            return false;
        }
        $append = preg_match('/\[\]$/', $key);
        if (($append && $parseInfo['cntEBrackets'] === 1) || (!$append && $parseInfo['cntEBrackets'] === 0)) {
            $evalStr = '';
            $next = false;
            foreach ($parseInfo['splitStr'] as $el) {
                $return = false;
                $prevEl = $evalStr;
                $evalStr .= '[\'' . $el . '\']';
                if ($next) {
                    continue;
                }
                $next = eval('return !array_key_exists(\'' . $el . '\',$array' . $prevEl . ');');
                if ($next) {
                    continue;
                }
                $next = eval('return !is_array($array' . $evalStr . ') && array_key_exists(\'' . $parseInfo['endKey'] . '\',$array' . $prevEl . ');');
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
        $parseInfo = self::parseAndValidateKeys($key);
        if ($parseInfo['isBroken'] || $parseInfo['cntEBrackets']) {
            return false;
        }
        $evalStr = self::getKeyStringForEval($parseInfo);
        return eval('if(is_array($array' . $parseInfo['prevEl'] . ') && array_key_exists(\'' . $parseInfo['endKey'] . '\',$array' . $parseInfo['prevEl'] . ')){unset($array' . $evalStr . ');return true;}else{return false;}');
    }

    /**
     * Get element of the array by a string representation
     *
     * @param string $key <p>Name key in the array. Example: key[sub_key][sub_sub_key]</p>
     * @param array $array <p>The array</p>
     * @param string $default [optional] <p>Default value if key not exist, default: null</p>
     * @param bool $ignoreString [optional] <p>Ignore string element as array, get only element, default: true</p>
     *
     * @return mixed returns value by a key, or default value otherwise
     */
    public static function get($key, $array, $default = null, $ignoreString = true)
    {
        if (!is_array($array)) {
            return $default;
        }
        if ($key == '') {
            if (!array_key_exists((string)$key, $array) || !is_array($array)) {
                return $default;
            }
            return $array[$key];
        }
        if ($key === '[]') {
            return $default;
        }
        $parseInfo = self::parseAndValidateKeys($key);
        if ($parseInfo['isBroken'] || $parseInfo['cntEBrackets']) {
            return $default;
        }
        $evalStr = self::getKeyStringForEval($parseInfo);
        if ($ignoreString) {
            return eval('return (is_array($array' . $parseInfo['prevEl'] . ') && array_key_exists(\'' . $parseInfo['endKey'] . '\',$array' . $parseInfo['prevEl'] . '))?$array' . $evalStr . ':$default;');
        }
        return eval('return ((is_array($array' . $parseInfo['prevEl'] . ') && array_key_exists(\'' . $parseInfo['endKey'] . '\',$array' . $parseInfo['prevEl'] . ')) || is_string($array' . $parseInfo['prevEl'] . '))?$array' . $evalStr . ':$default;');
    }

    private static function getKeyStringForEval($parseInfo)
    {
        return '[\'' . implode('\'][\'', $parseInfo['splitStr']) . '\']';
    }

    private static function parseAndValidateKeys($key)
    {
        $splitStr = [];
        $cntEBrackets = 0;
        $endKey = '';
        $prevEl = '';
        $isBroken = (bool)preg_replace_callback(array('/(?J:\[([\'"])(?<el>.*?)\1\]|(?<el>\]?[^\[]+)|\[(?<el>(?:[^\[\]]+|(?R))*)\])/'),
            function ($m) use (&$splitStr, &$cntEBrackets, &$endKey) {
                if ($m[0] == '[]') {
                    $cntEBrackets++;
                    return '';
                }
                $splitStr[] = $endKey = str_replace("'", "\\'", $m['el']);
                return '';
            }, $key);

        if (($cntEl = count($splitStr)) > 1) {
            $prevEl = '[\'' . implode('\'][\'', array_slice($splitStr, 0, $cntEl - 1)) . '\']';
        }
        return [
            'isBroken' => $isBroken,
            'cntEBrackets' => $cntEBrackets,
            'endKey' => $endKey,
            'splitStr' => $splitStr,
            'prevEl' => $prevEl
        ];
    }
}
