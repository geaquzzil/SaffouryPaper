<?php
$tablesNames = array();
//utils
function isNewRecord($object)
{
    $keyValue = isSetKeyFromObjReturnValue($object, 'iD');
    $boo = $keyValue == null || ($keyValue != null && $keyValue == -1);
    //   echo "isNewRecord ".$keyValue . ($boo ? " TRUE" : " FLAKSE \n");
    return $boo;
}
function array_sort($array, $on, $order = SORT_ASC)
{
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
                break;
            case SORT_DESC:
                arsort($sortable_array);
                break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}
function isSetKeyFromObjReturnValue($object, $key)
{
    return isSetKeyFromObj($object, $key) ? getKeyValueFromObj($object, $key) : null;
}
function isSetKeyAndNotNullFromObj($object, $key)
{
    if (gettype($object) === "object") {
        return
            property_exists($object, $key) && isset($object->{$key}) && !is_null($object->{$key});
    } else {
        return isset($object[$key]) && !is_null($object[$key]);
    }
}
function isSetKeyFromObj($object, $key)
{
    if (gettype($object) === "object") {
        return
            property_exists($object, $key) && isset($object->{$key});
    } else {
        return isset($object[$key]);
    }
}
function unSetKeyFromObj(&$object, $key)
{
    if (isSetKeyFromObj($object, $key)) {
        if (isObject($object)) {
            unset($object->$key);
        } else {
            unset($object[$key]);
        }
    }
}
function unSetKeyFromObjWithoutChecking(&$object, $key)
{
    if (isObject($object)) {
        unset($object->$key);
    } else {
        unset($object[$key]);
    }
}
function setKeyValueFromObj(&$object, $key, $value)
{
    if (gettype($object) === "object") {
        $object->{$key} = $value;
    } else {
        $object[$key] = $value;
    }
}
function getKeyValueFromObj($object, $key)
{
    if (isObject($object)) {
        return $object->$key;
    } else {
        return $object[$key];
    }
}
function convertToObjectByJson(&$object)
{
    $object = json_decode(json_encode($object), FALSE);
}
function convertToObject(&$object)
{
    if (is_array($object)) {
        //  echo "\n object is array convert to object \n";
        $soso = new stdClass();
        foreach ($object as $key => $value) {
            $soso->$key = $value;
        }
        $object = $soso;
    }
}
//end utils

function curdate()
{
    return date('Y-m-d');
}
//function isJson($string) {
//	if(IsNullOrEmptyString($string))return false;
//	if(is_null($string))return false;
//	if(is_array($string)){
//	    return $res;
//	}
//	$res=json_decode($string);
//	if(empty($res))return false;
//	return (json_last_error() == JSON_ERROR_NONE);
//}

function isJson($str)
{
    if (IsNullOrEmptyString($str)) return false;
    $json = json_decode($str);
    return $json && $str != $json;
}
function isMultiDimension($array)
{
    return (count($array) != count($array, 1));
}
function toBoolean($string)
{
    //echo $string;
    $string = strtolower($string);
    if (is_bool(jsonDecode($string))) {
        return true;
    }
    return false;
}
//removing < and > from request
function getTableColumnFromRequest($str)
{
    return substr($str, 1, -1);
}
function isBase64($s)
{
    if (empty($s) || is_null($s)) return false;
    if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s)) return false;
    return true;
}
function IsNullOrEmptyString($str)
{
    if (is_array($str)) return false;
    return (!isset($str) || trim($str) === '');
}
function isDateTime($x)
{
    return (date('Y-m-d H:i:s', strtotime($x)) == $x);
}
function isDate($x)
{
    return (date('Y-m-d', strtotime($x)) == $x);
}
function isObject($object)
{
    return gettype($object) === "object";
}
function isArray($object)
{
    return gettype($object) === "array";
}
function isTableColumn($str)
{
    return (substr($str, 0, 1) == "<" && substr($str, -1) == ">");
}
function has_perfix_reg($string, $reg)
{
    return eregi($reg, $string);
}
function has_prefix($string, $prefix)
{
    return substr($string, 0, strlen($prefix)) == $prefix;
}
function has_word($string, $word)
{
    return strpos($string, $word) !== false;
}
function isEmptyString($str)
{
    if (is_array($str)) return false;
    return (!isset($str) || trim($str) === '');
}
function jsonDecode($jsonPost)
{
    return json_decode($jsonPost, true);
}
function jsonEncode($jsonPost)
{
    return (json_encode($jsonPost));
}
function cloneByJson($object)
{
    return jsonDecode(jsonEncode($object));
}
//KEYS  primary key

//TABLE_COMMENT not VIEW if you want to show only tables


function getReqestMethod()
{
    return $_SERVER['REQUEST_METHOD'];
}

function getRequestValue($key)
{
    $method = getReqestMethod();
    switch ($method) {
        case 'GET':
            return $_GET[$key];
        case 'POST':
            return $_POST[$key];
        case 'DELETE':
            break;
        case 'PUT':
            break;
    }
}
function removeFromArray(&$array, $key)
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
function isActionTableIsP($tableName, $key)
{
    return $key === PARENTID ? false : isActionTableIs($tableName);
}
function isActionTableIs($tableName)
{
    return getRequestValue('table') === $tableName;
}
function isActionIsView()
{
    return getRequestValue('action') === 'view';
}
function isActionIsList()
{
    return getRequestValue('action') === 'list';
}
function isActionIsDelete()
{
    return getRequestValue('action') === 'delete';
}
function isActionIs($actionName)
{
    return getRequestValue('action') === $actionName;
}
function isTableIs($tableName)
{
    return  getRequestValue('table') === $tableName;
}
function checkRequestValueInt($key)
{
    return checkRequestValue($key) && is_numeric(getRequestValue($key));
}
function checkRequestValue($key)
{
    $method = getReqestMethod();
    switch ($method) {
        case 'GET':
            return isset($_GET[$key]) && !IsNullOrEmptyString($_GET[$key]);
        case 'POST':
            return isset($_POST[$key]) && !IsNullOrEmptyString($_POST[$key]);
        case 'DELETE':
            break;
        case 'PUT':
            break;
    }
}
function checkRequestValueWithoutCheckingEmpty($key)
{
    $method = getReqestMethod();
    switch ($method) {
        case 'GET':
            return isset($_GET[$key]);
        case 'POST':
            return isset($_POST[$key]);
        case 'DELETE':
            break;
        case 'PUT':
            break;
    }
}
function getListRequestAction()
{
    $myGetArgs = filter_input_array(INPUT_GET);
    $myPostArgs = filter_input_array(INPUT_POST);
    //	$myServerArgs = filter_input_array(INPUT_SERVER);
    //	print_r($myServerArgs);
    // $myCookieArgs = filter_input_array(INPUT_COOKIE);
    $method = getReqestMethod();
    switch ($method) {
        case 'GET':
            return $myGetArgs;
        case 'POST':
            return $myPostArgs;
        case 'DELETE':
            break;
        case 'PUT':
            break;
    }
}
//TODO this for return text for the uploaded file
function getFileTextFromRequest(&$txt)
{
    try {
        $maxSize = 10000000;
        // Undefined | Multiple Files | $_FILES Corruption Attack
        // If this request falls under any of them, treat it invalid.
        if (
            !isset($_FILES['upfile']['error']) ||
            is_array($_FILES['upfile']['error'])
        ) {
            $txt = "Invalid parameters.";
            return false;
            die;
        }

        // Check $_FILES['upfile']['error'] value.
        switch ($_FILES['upfile']['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                $txt = "No file sent.";
                return false;
                die;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $txt = "Exceeded filesize limit.";
                return false;
                die;
            default:
                $txt = "Unknown errors.";
                return false;
                die;
        }

        // You should also check filesize here.
        if ($_FILES['upfile']['size'] > 1000000) {
            $txt = "Exceeded filesize limit.";
            return false;
            die;
        }

        // $txt= file_get_contents($_FILES['upfile']['tmp_name']);
        $txt = gz_get_contents($_FILES['upfile']['tmp_name']);
        if (is_bool($txt)) {
            return $txt;
        }
        return true;
    } catch (RuntimeException $e) {
        $txt = $e->getMessage();
        return false;
    }
}
function getGzipFileFromRequest() {}
//TODO change it to move_upladed_file
function getFileFromRequest()
{
    try {
        $maxSize = 10000000;
        print_r($_FILES);
        // Undefined | Multiple Files | $_FILES Corruption Attack
        // If this request falls under any of them, treat it invalid.
        if (
            !isset($_FILES['upfile']['error']) ||
            is_array($_FILES['upfile']['error'])
        ) {
            //throw new RuntimeException('Invalid parameters.');
        }

        // Check $_FILES['upfile']['error'] value.
        switch ($_FILES['upfile']['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('No file sent.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('Exceeded filesize limit.');
            default:
                throw new RuntimeException('Unknown errors.');
        }

        // You should also check filesize here.
        if ($_FILES['upfile']['size'] > 1000000) {
            throw new RuntimeException('Exceeded filesize limit.');
        }

        echo file_get_contents($_FILES['upfile']['tmp_name']);

        // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
        // Check MIME Type by yourself.
        //  $finfo = new finfo(FILEINFO_MIME_TYPE);
        //    if (false === $ext = array_search(
        //      $finfo->file($_FILES['upfile']['tmp_name']),
        //        array(
        //          'jpg' => 'image/jpeg',
        //        'png' => 'image/png',
        //       'gif' => 'image/gif',
        //  ),
        //    true
        //    )) {
        //      throw new RuntimeException('Invalid file format.');
        //    }

        // You should name it uniquely.
        // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
        // On this example, obtain safe unique name from its binary data.
        if (!move_uploaded_file(
            $_FILES['upfile']['name'],
            sprintf(
                './files/%s.%s',
                sha1_file($_FILES['upfile']['name']),
                $ext
            )
        )) {
            throw new RuntimeException('Failed to move uploaded file.');
        }

        echo 'File is uploaded successfully.';
    } catch (RuntimeException $e) {
        echo $e->getMessage();
    }
}
function display_filesize($filesize)
{
    if (is_numeric($filesize)) {
        $decr = 1024;
        $step = 0;
        $prefix = array('Byte', 'KB', 'MB', 'GB', 'TB', 'PB');

        while (($filesize / $decr) > 0.9) {
            $filesize = $filesize / $decr;
            $step++;
        }
        return round($filesize, 2) . ' ' . $prefix[$step];
    } else {

        return 'NaN';
    }
}
function unlinkFile($filename)
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
function _encode_string_array($stringArray)
{
    $s = strtr(base64_encode(addslashes(gzcompress(serialize($stringArray), 9))), '+/=', '-_,');
    return $s;
}

function _decode_string_array($stringArray)
{
    $s = unserialize(gzuncompress(stripslashes(base64_decode(strtr($stringArray, '-_,', '+/=')))));
    return $s;
}
function checkMySqlSyntax($mysqli, $query)
{
    if (trim($query)) {
        // Replace characters within string literals that may *** up the process
        $query = replaceCharacterWithinQuotes($query, '#', '%');
        $query = replaceCharacterWithinQuotes($query, ';', ':');
        // Prepare the query to make a valid EXPLAIN query
        // Remove comments # comment ; or  # comment newline
        // Remove SET @var=val;
        // Remove empty statements
        // Remove last ;
        // Put EXPLAIN in front of every MySQL statement (separated by ;) 
        $query = "EXPLAIN " .
            preg_replace(
                array(
                    "/#[^\n\r;]*([\n\r;]|$)/",
                    "/[Ss][Ee][Tt]\s+\@[A-Za-z0-9_]+\s*=\s*[^;]+(;|$)/",
                    "/;\s*;/",
                    "/;\s*$/",
                    "/;/"
                ),
                array("", "", ";", "", "; EXPLAIN "),
                $query
            );

        foreach (explode(';', $query) as $q) {
            $result = $mysqli->query($q);
            $err = !$result ? $mysqli->error : false;
            if (! is_object($result) && ! $err) $err = "Unknown SQL error";
            if ($err) return $err;
        }
        return false;
    }
}

function replaceCharacterWithinQuotes($str, $char, $repl)
{
    if (strpos($str, $char) === false) return $str;

    $placeholder = chr(7);
    $inSingleQuote = false;
    $inDoubleQuotes = false;
    for ($p = 0; $p < strlen($str); $p++) {
        switch ($str[$p]) {
            case "'":
                if (! $inDoubleQuotes) $inSingleQuote = ! $inSingleQuote;
                break;
            case '"':
                if (! $inSingleQuote) $inDoubleQuotes = ! $inDoubleQuotes;
                break;
            case '\\':
                $p++;
                break;
            case $char:
                if ($inSingleQuote || $inDoubleQuotes) $str[$p] = $placeholder;
                break;
        }
    }
    return str_replace($placeholder, $repl, $str);
}
function gz_get_contents($path)
{
    if (`gzip -t $path 2>&1`) {
        echo "File is corrupt";
        exit;
    }
    $file = @gzopen($path, 'rb', false);
    if ($file) {
        // echo "gz_get_contents true";
        $data = '';
        while (!gzeof($file)) {
            $data .= gzread($file, 1024);
        }
        gzclose($file);
    } else {
        echo "gz_get_contents false";
        return false;
    }
    return $data;
}
