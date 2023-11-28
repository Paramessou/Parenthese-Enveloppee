<?php

namespace App\Service;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class RateLimiterService
{
    private $cache;
    const BLOCK_TIME = 60; // Durée de blocage de l'utilisateur en secondes
    public function __construct()
    {
        $this->cache = new FilesystemAdapter();
    }

    public function hasExceededMaxAttempts(string $username): bool
    {

        $cacheKey = md5($username);
        $attempts = $this->cache->getItem($cacheKey);
        var_dump($attempts);

        if ($attempts->isHit() && $attempts->get() >= 5) {
            var_dump($attempts->get());
            $lastAttemptTime = $this->getLastAttemptTime($cacheKey);
            $now = new \DateTime();
            if ($lastAttemptTime !== null && $now->getTimestamp() - $lastAttemptTime->getTimestamp() < self::BLOCK_TIME) {
                var_dump($now->getTimestamp() - $lastAttemptTime->getTimestamp() < self::BLOCK_TIME);
                return true; // Le temps de blocage n'a pas encore écoulé, donc bloquez l'authentification
            }
        }

        var_dump($attempts->get() >= 5);
        return false; // Le temps de blocage a écoulé ou l'utilisateur n'a pas dépassé le nombre maximum de tentatives, donc ne bloquez pas l'authentification
    }

    public function incrementAttempts(string $username): void
    {
        $cacheKey = md5($username);
        $attempts = $this->cache->getItem($cacheKey);
        var_dump($attempts);

        if (!$attempts->isHit()) {
            var_dump('pas hit');
            $attempts->set(1);
        } else {
            var_dump('hit');
            $attempts->set($attempts->get() + 1);
        }

        $attempts->expiresAfter(self::BLOCK_TIME);
        $this->cache->save($attempts);

        $lastAttemptTime = $this->cache->getItem($cacheKey . '_last_attempt');
        $lastAttemptTime->set(time());
        $this->cache->save($lastAttemptTime);

        var_dump($attempts);
    }

    public function resetAttempts(string $username): void
    {
        $cacheKey = md5($username);
        $this->cache->delete($cacheKey);
    }

    public function getLastAttemptTime(string $username): ?\DateTime
    {
        $cacheKey = md5($username);
        $lastAttemptTime = $this->cache->getItem($cacheKey . '_last_attempt');

        if ($lastAttemptTime->isHit()) {
            return new \DateTime('@' . $lastAttemptTime->get());
        }

        var_dump($lastAttemptTime);
        var_dump($lastAttemptTime->isHit());
        return null;
    }

    public function setLastAttemptTime(string $username, int $time): void
    {
        $cacheKey = md5($username);
        $item = $this->cache->getItem($cacheKey . '_last_attempt');
        $item->set(time());
        $item->expiresAfter(self::BLOCK_TIME);
        $this->cache->save($item);
    }

    public function isBlockTimeElapsed(string $username): bool
    {
        $lastAttemptTime = $this->getLastAttemptTime($username);

        if ($lastAttemptTime === null) {
            return true;
        }

        $now = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $totalSeconds = $now->getTimestamp() - $lastAttemptTime->getTimestamp();

        // var_dump($lastAttemptTime);
        // var_dump($totalSeconds);
        return $totalSeconds >= self::BLOCK_TIME;
    }
}
