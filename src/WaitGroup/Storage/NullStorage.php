<?php
declare(strict_types=1);

namespace Sync\WaitGroup\Storage;

/**
 * Class NullStorage
 */
final class NullStorage implements StorageInterface
{
    /**
     * {@inheritdoc}
     */
    public function insertWaitGroup(string $uid): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function fetchWaitGroup(string $uid): ?array
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function updateCounter(string $uid, int $delta): void
    {
    }
}
