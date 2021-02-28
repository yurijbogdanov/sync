<?php
declare(strict_types=1);

namespace Sync\WaitGroup;

use Sync\WaitGroup\Exception\WaitGroupNotFoundException;
use Sync\WaitGroup\Exception\WaitGroupStorageException;
use Sync\WaitGroup\Storage\StorageInterface;

/**
 * Class WaitGroupFactory.
 */
final class WaitGroupFactory
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @throws WaitGroupStorageException
     *
     * @return WaitGroup
     */
    public function create(): WaitGroup
    {
        return new WaitGroup($this->storage);
    }

    /**
     * @param string $uid
     *
     * @throws WaitGroupStorageException
     * @throws WaitGroupNotFoundException
     *
     * @return WaitGroup
     */
    public function restore(string $uid): WaitGroup
    {
        return new WaitGroup($this->storage, $uid);
    }
}
