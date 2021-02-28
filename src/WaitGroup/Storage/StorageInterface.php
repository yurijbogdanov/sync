<?php
declare(strict_types=1);

namespace Sync\WaitGroup\Storage;

use Sync\WaitGroup\Exception\WaitGroupStorageException;

/**
 * Interface StorageInterface.
 */
interface StorageInterface
{
    /**
     * @param string $uid
     *
     * @throws WaitGroupStorageException
     *
     * @return void
     */
    public function insertWaitGroup(string $uid): void;

    /**
     * @param string $uid
     *
     * @throws WaitGroupStorageException
     *
     * @return array|null WaitGroup or NULL
     */
    public function fetchWaitGroup(string $uid): ?array;

    /**
     * @param string $uid
     * @param int $delta
     *
     * @throws WaitGroupStorageException
     *
     * @return void
     */
    public function updateCounter(string $uid, int $delta): void;
}
