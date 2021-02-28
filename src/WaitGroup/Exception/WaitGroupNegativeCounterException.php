<?php
declare(strict_types=1);

namespace Sync\WaitGroup\Exception;

/**
 * Class WaitGroupNegativeCounterException.
 */
final class WaitGroupNegativeCounterException extends WaitGroupException
{
    /**
     * @var string
     */
    private $uid;

    /**
     * @var int
     */
    private $counter;

    /**
     * @param string $uid
     * @param int $counter
     */
    public function __construct(string $uid, int $counter)
    {
        $this->uid = $uid;
        $this->counter = $counter;

        parent::__construct(\sprintf(
            'Sync WaitGroup: entity "%s" has negative counter "%s"',
            $this->getUid(),
            $this->getCounter()
        ));
    }

    /**
     * @return string
     */
    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * @return int
     */
    public function getCounter(): int
    {
        return $this->counter;
    }
}
