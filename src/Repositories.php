<?php

use Etq\Restful\Repository\Repository;
use Etq\Restful\Repository\UserRepository;
use Etq\Restful\Repository\PermissionRepository;
use Etq\Restful\Repository\NotificationRepository;
use Psr\Container\ContainerInterface;

$container['repository'] = static fn(
    ContainerInterface $container
): Repository => new Repository($container->get('db'));

$container['user_repository'] = static fn(
    ContainerInterface $container
): UserRepository => new UserRepository($container->get('db'));


$container['permission_repository'] = static fn(
    ContainerInterface $container
): PermissionRepository => new PermissionRepository($container->get('db'));

$container['notification_repository'] = static fn(
    ContainerInterface $container
): NotificationRepository => new NotificationRepository($container->get('db'));


