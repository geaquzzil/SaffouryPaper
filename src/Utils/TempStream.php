<?php

namespace Etq\Restful\Utils;
/*
 * Stream a temp file. Delete it when finished
 */

use Slim\Http\Stream;

class TempStream extends Stream
{
    private $filename;

    public function __construct($filename)
    {
        $this->filename = $filename;
        $fh = fopen($filename, 'rb');
        parent::__construct($fh);
    }

    public function __destruct()
    {
        // unlink($this->filename);
    }
}
