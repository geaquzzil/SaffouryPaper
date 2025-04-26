<?php

namespace Etq\Restful;

class Helpers
{
    /**
     * Checks if all keys in an array are in another array and their values are not empty string.
     *
     * @param array $requiredStrings
     * @param array $searchData
     *
     * @return bool
     */
    public static function keysExistAndNotEmptyString($requiredStrings, $searchData)
    {
        foreach ($requiredStrings as $key => $value) {
            if (!self::keyExistAndNotEmptyString($value, $searchData)) {
                return false;
            }
        }
        return true;
    }
    /**
     * Checks if a key is in an array and the value of the key is not an empty string.
     *
     * @param array $key
     * @param array $searchData
     *
     * @return bool
     */
    public static function keyExistAndNotEmptyString($key, $searchData)
    {
        return isset($searchData[$key]) && !empty($searchData[$key]) && is_string($searchData[$key]) && trim($searchData[$key]);
    }
    public static function isJson($str)
    {
        if (self::IsNullOrEmptyString($str)) return false;
        $json = json_decode($str);
        return $json && $str != $json;
    }
    public static function isSetKeyFromObjReturnValue($object, $key)
    {
        return  self::isSetKeyFromObj($object, $key) ? self::getKeyValueFromObj($object, $key) : null;
    }
    public static function toBoolean($string)
    {
        //echo $string;
        $string = strtolower($string);
        if (is_bool(self::jsonDecode($string))) {
            return true;
        }
        return false;
    }
    public static function isIntReturnValue($value): ?int
    {
        return is_numeric($value) ? intval($value) : null;
    }
    public static function isNewRecord($object)
    {
        $keyValue = self::isSetKeyFromObjReturnValue($object, 'iD');
        $boo = $keyValue == null || ($keyValue != null && $keyValue == -1);
        return $boo;
    }
    public static function explodeURIGetTableName($url)
    {
        $arr = explode('/', $url);
        echo count($arr) . "\n";

        //todo this not working if api/v1/product/221 it returns 221
        return $arr[2];
    }
    public static function explodeURIGetID($url)
    {
        $arr = explode('/', $url);
        echo count($arr) . "\n";

        //todo this not working if api/v1/product/221 it returns 221
        return $arr[3];
    }
    public static function isSetKeyFromObj($object, $key)
    {
        if (self::isObject($object)) {
            return
                property_exists($object, $key) && isset($object->{$key});
        } else {
            return isset($object[$key]);
        }
    }
    public static function unSetKeyFromObj(&$object, $key)
    {
        if (self::isSetKeyFromObj($object, $key)) {
            if (self::isObject($object)) {
                unset($object->$key);
            } else {
                unset($object[$key]);
            }
        }
    }
    public static function getKeyValueFromObj($object, $key)
    {
        if (self::isObject($object)) {
            return $object->$key;
        } else {
            return $object[$key];
        }
    }
    public static function isSetKeyAndNotNullFromObj($object, $key)
    {
        if (gettype($object) === "object") {
            return
                property_exists($object, $key) && isset($object->{$key}) && !is_null($object->{$key});
        } else {
            return isset($object[$key]) && !is_null($object[$key]);
        }
    }
    public static  function setKeyValueFromObj(&$object, $key, $value)
    {
        if (gettype($object) === "object") {
            $object->{$key} = $value;
        } else {
            $object[$key] = $value;
        }
    }

    public static function isBase64($s)
    {
        if (empty($s) || is_null($s)) return false;
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s)) return false;
        return true;
    }
    public static  function IsNullOrEmptyString($str)
    {
        if (is_array($str)) return false;
        return (!isset($str) || trim($str) === '');
    }
    public static function isDateTime($x)
    {
        return (date('Y-m-d H:i:s', strtotime($x)) == $x);
    }
    public static function isDate($x)
    {
        return (date('Y-m-d', strtotime($x)) == $x);
    }
    public static  function isObject($object)
    {
        return gettype($object) === "object";
    }
    public static  function isArray($object)
    {
        return gettype($object) === "array";
    }
    public static  function isTableColumn($str)
    {
        return (substr($str, 0, 1) == "<" && substr($str, -1) == ">");
    }
    public static function has_perfix_reg($string, $reg)
    {
        return eregi($reg, $string);
    }
    public static function unlinkFile($filename)
    {
        if (is_link($filename)) {
            $sym = @readlink($filename);
            if ($sym) {
                return is_writable($filename) && @unlink($filename);
            }
        }
        if (realpath($filename) && realpath($filename) !== $filename) {
            return is_writable($filename) && @unlink(realpath($filename));
        }
        return is_writable($filename) && @unlink($filename);
    }
    public static  function has_prefix($string, $prefix)
    {
        return substr($string, 0, strlen($prefix)) == $prefix;
    }
    public static  function has_word($string, $word)
    {
        return strpos($string, $word) !== false;
    }
    public static function isEmptyString($str)
    {
        if (is_array($str)) return false;
        return (!isset($str) || trim($str) === '');
    }
    public static   function jsonDecode($jsonPost)
    {
        return json_decode($jsonPost, true);
    }
    public static function jsonEncode($jsonPost)
    {
        return json_encode($jsonPost);
    }
    public static  function cloneByJson($object)
    {
        return jsonDecode(jsonEncode($object));
    }
    public static function removeFromArray(&$array, $key)
    {
        if (is_bool($array)) {
            return $array;
        }
        $index = array_search($key, $array);
        if ($index !== FALSE) {
            unset($array[$index]);
        }
        return $array;
    }
    ///
    public static function isBoolean($value)
    {
        if ($value === '') {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
