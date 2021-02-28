<?php
declare(strict_types=1);

namespace Sync\WaitGroup;

use Sync\WaitGroup\Exception\WaitGroupNegativeCounterException;
use Sync\WaitGroup\Exception\WaitGroupNotFoundException;
use Sync\WaitGroup\Exception\WaitGroupStorageException;
use Sync\WaitGroup\Storage\StorageInterface;

/**
 * Class WaitGroup.
 */
final class WaitGroup
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var string
     */
    private $uid;

    /**
     * @param StorageInterface $storage
     * @param string $uid
     *
     * @throws WaitGroupStorageException
     * @throws WaitGroupNotFoundException
     */
    public function __construct(StorageInterface $storage, string $uid = '')
    {
        $this->storage = $storage;

        if ('' === $uid) {
            $this->uid = $this->generateUid();
            $this->storage->insertWaitGroup($this->getUid());
        } else {
            $this->uid = $uid;
            $this->getWaitGroup();
        }
    }

    /**
     * @param int $delta
     *
     * @throws WaitGroupStorageException
     * @throws WaitGroupNegativeCounterException
     *
     * @return void
     */
    public function add(int $delta): void
    {
        $this->storage->updateCounter($this->getUid(), $delta);

        if (($counter = $this->getCounter()) < 0) {
            throw new WaitGroupNegativeCounterException($this->getUid(), $counter);
        }
    }

    /**
     * @throws WaitGroupStorageException
     * @throws WaitGroupNegativeCounterException
     *
     * @return void
     */
    public function done(): void
    {
        $this->add(-1);
    }

    /**
     * @param int $timeout
     * @param int $sleep
     *
     * @throws WaitGroupStorageException
     *
     * @return bool
     */
    public function wait(int $timeout = -1, int $sleep = 5): bool
    {
        if ($sleep < 0) {
            $sleep = 0;
        }

        if ($timeout > 0 && $timeout < $sleep) {
            $sleep = $timeout;
        }

        $start = microtime(true);

        while (true) {
            $counter = $this->getCounter();

            if (0 === $counter) {
                break;
            }

            if ($counter < 0) {
                throw new WaitGroupNegativeCounterException($this->getUid(), $counter);
            }

            if ($sleep > 0) {
                \sleep($sleep);
            }

            if ($timeout > -1) {
                $diff = microtime(true) - $start;
                if ($diff >= $timeout) {
                    break;
                }
            }
        }

        return $counter <= 0;
    }

    /**
     * @throws WaitGroupStorageException
     * @throws WaitGroupNotFoundException
     *
     * @return int
     */
    public function getCounter(): int
    {
        return (int)$this->getWaitGroup()['counter'];
    }

    /**
     * @return string
     */
    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * @return string
     */
    private function generateUid(): string
    {
        return sha1(uniqid('sync_waitgroup', true));
    }

    /**
     * @throws WaitGroupNotFoundException
     *
     * @return array
     */
    private function getWaitGroup(): array
    {
        if (null === $wg = $this->storage->fetchWaitGroup($this->getUid())) {
            throw new WaitGroupNotFoundException($this->getUid());
        }

        return $wg;
    }
}
