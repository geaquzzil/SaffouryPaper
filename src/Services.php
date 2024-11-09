<?php

use Psr\Container\ContainerInterface;
use Etq\Restful\Service\Service;

$container['services'] = static fn(
    ContainerInterface $container
): Service => new Service($container->get('redis_service'));
