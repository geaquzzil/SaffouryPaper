<?php

$container[SEARCH][SIZE] = function (&$object, $container) {
    $container['fund_repository']->checkToDeleteJournal($object);
    checkToDeleteJournal($object);
};
