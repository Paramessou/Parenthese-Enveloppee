<?php

namespace App\Service;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class RateLimiterService
{
    private $cache;

    public function __construct()
    {
        $this->cache = new FilesystemAdapter();
    }

    public function hasExceededMaxAttempts(string $username): bool
    {
        $attempts = $this->cache->getItem($username);
        // print_r($attempts);
        // var_dump($attempts->isHit(), $attempts->get() >= 5);
        return $attempts->isHit() && $attempts->get() >= 5;
    }

    public function incrementAttempts(string $username): void
    {
        $attempts = $this->cache->getItem($username);
        // print_r($attempts);

        if (!$attempts->isHit()) {
            // print_r($attempts);
            $attempts->set(1);
        } else {
            // print_r($attempts);
            $attempts->set($attempts->get() + 1);
        }

        $this->cache->save($attempts);
    }

    public function resetAttempts(string $username): void
    {
        $this->cache->delete($username);
    }
}
