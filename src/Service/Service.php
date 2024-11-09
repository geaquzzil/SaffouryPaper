<?php

declare(strict_types=1);

namespace Etq\Restful\Service;

use Etq\Restful\Repository\Repository;

class Service extends BaseService
{
    private const REDIS_KEY = 'user:%s';

    public function __construct(
        protected RedisService $redisService,
        protected Repository $repository,
    ) {}

    protected function getFromCache($key, ?string $query = null): object
    {
        $redisKey = sprintf(self::REDIS_KEY,);
        $key = $this->redisService->generateKey($redisKey);
        if ($this->redisService->exists($key)) {
            $data = $this->redisService->get($key);
            $user = json_decode((string) json_encode($data), false);
        } else {
            $user = $this->getFromDb($query)->toJson();
            $this->redisService->setex($key, $user);
        }

        return $user;
    }

    protected function getFromDb(string $query)
    {
        return $this->repository->getUser($userId);
    }

    protected function saveInCache(int $key, object $objcet): void
    {
        $redisKey = sprintf(self::REDIS_KEY, $objcet);
        $key = $this->redisService->generateKey($redisKey);
        $this->redisService->setex($key, $objcet);
    }

    protected function deleteFromCache(int $key): void
    {
        $redisKey = sprintf(self::REDIS_KEY, $key);
        $key = $this->redisService->generateKey($redisKey);
        $this->redisService->del([$key]);
    }
}
