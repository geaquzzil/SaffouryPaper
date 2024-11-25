<?php

namespace Etq\Restful\Security;

class MCrypt
{

    private $key;
    private $vectorLength;
    private $cipher;
    function __construct()
    {

        $this->key = $_SERVER['M_KEY'];
        $this->vectorLength = $_SERVER['M_VECT'];
        $this->cipher = $_SERVER['M_CIPHER'];
    }
    /**
     * @param string $str
     * @param bool $isBinary whether to encrypt as binary or not. Default is: false
     * @return string Encrypted data
     */
    public function encrypt($plainText)
    {
        try {
            // Check secret length
            // Get random initialization vector
            $initVector = bin2hex(openssl_random_pseudo_bytes($this->vectorLength / 2));
            // Encrypt input text
            $raw = openssl_encrypt(
                $plainText,
                $this->cipher,
                $this->key,
                OPENSSL_RAW_DATA,
                $initVector
            );
            // Return base64-encoded string: initVector + encrypted result
            $result = base64_encode($initVector . $raw);
            if ($result === false) { // Operation failed
                return openssl_error_string();
            }
            // Return successful encoded object
            return $result;
        } catch (\Exception $e) { // Operation failed
            return new static(isset($initVector), null, $e->getMessage());
        }
    }
    /**
     * @param string $code
     * @param bool $isBinary whether to decrypt as binary or not. Default is: false
     * @return string Decrypted data
     */
    public function decrypt($code, $isBinary = false)
    {
        // Get raw encoded data
        $encoded = base64_decode($code);
        // Slice initialization vector
        $initVector = substr($encoded, 0, 16);
        // Slice encoded data
        $data = substr($encoded, 16);
        $decoded = openssl_decrypt(
            $data,
            $this->cipher,
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
    protected function hex2bin($hexdata)
    {
        $bindata = '';
        for ($i = 0; $i < strlen($hexdata); $i += 2) {
            $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
        }
        return $bindata;
    }
}
