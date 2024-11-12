<?php

use Etq\Restful\Repository\Repository;
use Etq\Restful\Repository\UserRepository;
use Psr\Container\ContainerInterface;

$container['repository'] = static fn(
    ContainerInterface $container
): Repository => new Repository($container->get('db'));


$container['user_repository'] = static fn(
    ContainerInterface $container
): UserRepository => new UserRepository($container->get('db'));
