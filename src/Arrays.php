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
        return self::parseAndValidateKeys($key, $array)['isExists'];
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
        $parseInfo = self::parseAndValidateKeys($key, $array, 'save');
        if ($parseInfo['completed']) {
            $currEl = &$array;
            foreach ($parseInfo['keys'] as $key) {
                if (!array_key_exists((string)$key, (array)$currEl)) {
                    $mCurSource[$key] = [];
                }
                $currEl = &$currEl[$key];
            }
            if ($parseInfo['append']) {
                $currEl[] = $value;
            } else {
                $currEl = $value;
            }
        }
        return $parseInfo['completed'];
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
        return self::parseAndValidateKeys($key, $array, 'delete')['completed'];
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
        $parseInfo = self::parseAndValidateKeys($key, $array, 'get');
        if ($ignoreString) {
            return (!$parseInfo['isString'] && $parseInfo['completed']) ? $parseInfo['value'] : $default;
        }
        return ($parseInfo['completed'] && ($parseInfo['isExists'] || $parseInfo['isString'])) ? $parseInfo['value'] : $default;
    }

    private static function parseAndValidateKeys($key, &$array = null, $mode = 'exists')
    {
        $parseInfo = [
            'keys' => [],
            'lastKey' => '',
            'prevEl' => &$array,
            'currEl' => &$array,
            'isExists' => null,
            'cntEBrackets' => 0,
            'isString' => false,
            'completed' => false,
            'first' => true,
            'append' => false,
            'value' => null
        ];
        $parseInfo['isBroken'] = (bool)preg_replace_callback(array('/(?J:\[([\'"])(?<el>.*?)\1\]|(?<el>\]?[^\[]+)|\[(?<el>(?:[^\[\]]+|(?R))*)\])/'),
            function ($m) use (&$parseInfo, &$array) {
                if ($m[0] == '[]') {
                    $parseInfo['isExists'] = false;
                    $parseInfo['cntEBrackets']++;
                    $parseInfo['append'] = $parseInfo['cntEBrackets'] == 1;
                    return '';
                }
                $parseInfo['append'] = false;
                if ($parseInfo['isExists'] !== false) {
                    if (!is_array($parseInfo['currEl'])) {
                        $parseInfo['isExists'] = false;
                        $parseInfo['lastKey'] = $m['el'];
                        return '';
                    }
                    if (($parseInfo['isExists'] = array_key_exists((string)$m['el'],
                            $parseInfo['currEl']) && is_array($parseInfo['currEl']))
                    ) {
                        if (!$parseInfo['first']) {
                            $parseInfo['prevEl'] = &$parseInfo['currEl'];
                        }
                        $parseInfo['currEl'] = &$array[$m['el']];
                        $parseInfo['lastKey'] = $m['el'];
                        $parseInfo['first'] = false;
                    }
                }
                $parseInfo['keys'][] = $m['el'];
                return '';
            }, $key);
        if ($parseInfo['isExists'] === false && is_array($parseInfo['prevEl']) && is_string($parseInfo['currEl'])) {
            $parseInfo['isString'] = true;
            if ($mode == 'get' && isset($parseInfo['currEl'][$parseInfo['lastKey']])) {
                $parseInfo['completed'] = true;
                $parseInfo['value'] = $parseInfo['currEl'][$parseInfo['lastKey']];
            }
        }
        if ($mode == 'get' && $parseInfo['isExists']) {
            $parseInfo['completed'] = true;
            $parseInfo['value'] = $parseInfo['prevEl'][$parseInfo['lastKey']];
        }
        if ($mode == 'delete' && $parseInfo['isExists']) {
            unset($parseInfo['prevEl'][$parseInfo['lastKey']]);
            $parseInfo['completed'] = true;
        }
        if ($mode == 'save') {
            if (!$parseInfo['isExists'] && is_array($parseInfo['prevEl']) && $parseInfo['cntEBrackets'] <= 1) {
                $parseInfo['completed'] = true;
            }
        }
        if ($parseInfo['isBroken']) {
            $parseInfo['completed'] = false;
        }
        return $parseInfo;
    }
}
