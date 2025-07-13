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
    public static function convertToObject(&$object)
    {
        if (is_array($object)) {
            //  echo "\n object is array convert to object \n";
            $soso = new \stdClass();
            foreach ($object as $key => $value) {
                $soso->$key = $value;
            }
            $object = $soso;
        }
    }

    public static function isMultiDimensionalArray(array $array): bool
    {
        foreach ($array as $element) {
            if (is_array($element)) {
                return true;
            }
        }
        return false;
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
    public static function explodeURIGetTableName($url, int $pos = 2)
    {

        if (count(explode('/', $url)) < $pos) {
            return "";
        }
        $arr = explode('/', $url);
        return $arr[$pos];
    }

    public static function isSetKeyFromObj($object, $key)
    {
        if (self::isObject($object)) {
            return
                property_exists($object, $key);
        } else {
            return array_key_exists($key, $object);
        }
    }
    public static function unSetKeyFromObj(&$object, $key)
    {
        // echo "\checking set $key\n";
        if (self::isSetKeyFromObj($object, $key)) {
            // echo "\nis set $key\n";
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
    public static function getIDFromArray($array)
    {
        return array_map(function ($tmp) {
            return $tmp['iD'];
        }, $array);
    }
    public static function getIDSImpolde($array, $keyToFind, $getValueFromArray = false)
    {
        if ($getValueFromArray) {
            $array = array_map(function ($tmp) {
                return $tmp['iD'];
            }, $array);
        }
        $ids = implode("','", $array);
        return "$keyToFind IN ( '" . $ids . "' )";
    }
    public static function isArrayByJson($object)
    {
        $object = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', json_encode($object)));
        return self::isArray($object);
    }
    public static  function isArray($object)
    {
        return gettype($object) === "array";
    }
    public static  function isTableColumn($str)
    {
        return (substr($str, 0, 1) == "<" && substr($str, -1) == ">");
    }
    ///set arr2 to arr1
    public static function setValuesThatNotFoundInTowArray($arr1, $arr2)
    {
        return array_diff($arr1, $arr2);
    }
    public static function setImage(&$object)
    {
        if (self::isBase64(
            self::isSetKeyAndNotNullFromObj($object, "image")
        )) {
            self::unSetKeyFromObj($object, "delete");
            $filename_path = md5(time() . uniqid()) . ".jpg";
            $base64_string = str_replace('data:image/png;base64,', '', $object->image);
            $base64_string = str_replace(' ', '+', $object->image);
            $decoded = base64_decode($base64_string);
            file_put_contents("Images/" . $filename_path, $decoded);
            self::setKeyValueFromObj($object, "image", $filename_path);
        }
    }

    public static function removeDuplicatesAndAdd($array, $key, $keyToAdd)
    {
        $filtered = array();
        $sa = array_column($array, $key, null);
        $dup = Helpers::getDuplicates(($sa));
        if (empty($dup)) return $array;
        $foundedInt = [];
        foreach ($sa as $i => $v) {
            if (Helpers::searchInArray($i, $foundedInt)) {
                echo "\nFounded before\n";
                continue;
            }
            foreach ($sa as $j => $v2) {
                if ($v == $v2 && $i != $j) {
                    $foundedInt[] = $j;
                    echo "\nfounded i $i j $j\n";

                    $arrToF =
                        array_merge(array_values($array[$i][$keyToAdd]), array_values($array[$j][$keyToAdd]));
                    $filtered[$i] = $array[$i];
                    $array[$i][$keyToAdd] = $arrToF;
                } else {
                    $filtered[$i] = $array[$i];
                }
            }
        }
        // print_r($filtered);
        // $arr =  array_values(array_column($array, null, ID));
        return $filtered;
    }

    //     function remove_duplicates($playlist){
    //     $filtered = [];
    //     foreach($playlist as $music){
    //         $array_exist = array_filter($filtered, function($val) use ($music) {
    //             return ($val['ARTIST'] === $music['ARTIST']) && 
    //                    ($val['TITLE'] === $music['TITLE']);
    //         });

    //         if( empty($array_exist) ){
    //             $filtered[] = $music;
    //         }else{
    //             foreach($array_exist as $index => $arr){
    //                 $filtered[$index]['REPEAT'] += 1;
    //             }
    //         }
    //     }
    //     return $filtered;
    // }
    public static function getDuplicates($array)
    {
        return array_diff_key($array, array_unique($array));
    }

    public  static function isEqualsString($string1, $string2)
    {
        return strcmp($string1, $string2) == 0;
    }
    public static function removeAllNonFoundInTowArray($arr1, $arr2, ?bool $getArrayByValues = true, &$removedItmes = [])
    {
        $arr = (
            array_filter($arr1, function ($value) use ($arr2, &$removedItmes) {
                $res = array_search($value, $arr2) !== false;
                if (!$res) {
                    $removedItmes[] = $value;
                }
                return $res;
            })
        );
        if ($getArrayByValues) {
            return array_values($arr);
        }
        return $arr;
    }
    public static function searchInArrayGetValue($search, $array, $column_key)
    {
        // print_r($array);
        $index = array_search($search,  array_column($array, $column_key));
        if ($index !== FALSE) {
            return Helpers::getKeyValueFromObj($array, $index);
        }
        return null;
    }
    public static function searchInArrayGetIndex($search, $array, $column_key = null)
    {
        // print_r($array);
        $index = array_search($search, $column_key ?   array_column($array, $column_key) : $array);
        if ($index !== FALSE) {
            return $index;
        }
        return null;
    }
    public static function searchInArray($search, $arr)
    {
        if (is_null($arr)) return false;
        return array_search($search, $arr) !== false;
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
        return self::jsonDecode(self::jsonEncode($object));
    }
    public static function removeFromArray(&$array, $key, bool &$isFounded = false)
    {
        if (is_bool($array)) {
            return $array;
        }
        $index = array_search($key, $array);
        if ($index !== FALSE) {

            $isFounded = true;
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
