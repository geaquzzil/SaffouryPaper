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
$container[PR] = [
    BEFORE . LISTO => function (&$object, ?Options &$option, BaseRepository $reo) {
        if ($option->notFoundedColumns->get("requiresInventory", null)) {
            echo "\n inside container  before listo requiresInventory";
        }
    },
    AFTER . VIEW => function (&$object, ?Options &$option, BaseRepository $reo) {
        if ($option->notFoundedColumns->get("requiresInventory", null)) {
            echo "\n inside container  after view requiresInventory";
        }
    },
];
