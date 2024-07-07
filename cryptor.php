<?php
class MCrypt
{
    private $iv = 'qusasafo!!@@##98'; 
    private $key = 'qusasafo12345&!#'; 
	const CIPHER = 'AES-128-CBC';
    const INIT_VECTOR_LENGTH = 16;
    function __construct(){}
/**
* @param string $str
* @param bool $isBinary whether to encrypt as binary or not. Default is: false
* @return string Encrypted data
*/
function encrypt($plainText){
    try {
		// Check secret length
        // Get random initialization vector
        $initVector = bin2hex(openssl_random_pseudo_bytes(static::INIT_VECTOR_LENGTH / 2));
        // Encrypt input text
        $raw = openssl_encrypt(
			$plainText,
            static::CIPHER,
			$this->key,
            OPENSSL_RAW_DATA,
            $initVector
        );
        // Return base64-encoded string: initVector + encrypted result
        $result = base64_encode($initVector . $raw);
        if ($result === false) {// Operation failed
		return openssl_error_string();}
        // Return successful encoded object
        return $result;
        }catch (\Exception $e) {// Operation failed
		return new static(isset($initVector), null, $e->getMessage());}
}
/**
* @param string $code
* @param bool $isBinary whether to decrypt as binary or not. Default is: false
* @return string Decrypted data
*/
function decrypt($code, $isBinary = false){
    // Get raw encoded data
    $encoded = base64_decode($code);
    // Slice initialization vector
    $initVector = substr($encoded, 0, 16);
    // Slice encoded data
    $data = substr($encoded, 16);
	$decoded = openssl_decrypt(
        $data,
        'AES-128-CBC',
        $this->key,
        OPENSSL_RAW_DATA,
        $initVector
    );
	if ($decoded === false) {
        // Operation failed
        return openssl_error_string();
    }
    // echo $decoded;
	return $decoded;
}
function decryptPAD($code) {
    //   $code = $this->hex2bin($code);
	$iv = $this->iv; 
	$td = mcrypt_module_open('rijndael-128', '', 'cbc', $iv); 
	mcrypt_generic_init($td, $this->key, $iv);
	$decrypted = mdecrypt_generic($td, $code); 
	mcrypt_generic_deinit($td);
	mcrypt_module_close($td); 
	$ut =  utf8_encode(trim($decrypted));
	return $this->pkcs5_unpad($ut);
}
private function pkcs5_unpad($text){ $pad = ord($text[strlen($text)-1]);return substr($text, 0, -1 * $pad);}
protected function hex2bin($hexdata){
    $bindata = '';
    for ($i = 0; $i < strlen($hexdata); $i += 2) {$bindata .= chr(hexdec(substr($hexdata, $i, 2))); }
     return $bindata;
}
}
?>