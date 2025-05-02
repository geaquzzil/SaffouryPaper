<?php

use Etq\Restful\Repository\CustomerRepository;
use Etq\Restful\Repository\DashboardRepository;
use Etq\Restful\Repository\Repository;
use Etq\Restful\Repository\UserRepository;
use Etq\Restful\Repository\PermissionRepository;
use Etq\Restful\Repository\NotificationRepository;
use Etq\Restful\Repository\ProductRepository;
use Psr\Container\ContainerInterface;

$container['repository'] = static fn(
    ContainerInterface $container
): Repository => new Repository($container->get('db'), $container);

$container['user_repository'] = static fn(
    ContainerInterface $container
): UserRepository => new UserRepository($container->get('db'), $container);
$container['customer_repository'] = static fn(
    ContainerInterface $container
): CustomerRepository => new CustomerRepository($container->get('db'), $container);
$container['dashboard_repository'] = static fn(
    ContainerInterface $container
): DashboardRepository => new DashboardRepository($container->get('db'), $container);
$container['product_repository'] = static fn(
    ContainerInterface $container
): ProductRepository => new ProductRepository($container->get('db'), $container);

$container['permission_repository'] = static fn(
    ContainerInterface $container
): PermissionRepository => new PermissionRepository($container->get('db'), $container);

$container['notification_repository'] = static fn(
    ContainerInterface $container
): NotificationRepository => new NotificationRepository($container->get('db'), $container);
