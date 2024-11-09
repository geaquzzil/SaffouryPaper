<?php

use Etq\Restful\Repository\Repository;
use Psr\Container\ContainerInterface;

$container['repository'] = static fn(
    ContainerInterface $container
): Repository => new Repository($container->get('db'));
