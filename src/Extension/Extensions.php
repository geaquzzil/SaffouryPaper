<?php

use Etq\Restful\Repository\BaseRepository;
use Etq\Restful\Repository\Options;

$container[PURCH] = [
    BEFORE . VIEW => function (&$object, ?Options &$option, BaseRepository $reo) {
        echo "\n inside container  before VIEW";
    },
    AFTER . VIEW => function (&$object, ?Options &$option, BaseRepository $reo) {
        echo "\n inside container after VIEW";
    },
];
