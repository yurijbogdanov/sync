<?php
declare(strict_types=1);

namespace Sync\WaitGroup\Exception;

/**
 * Class WaitGroupNotFoundException.
 */
final class WaitGroupNotFoundException extends WaitGroupException
{
    /**
     * @var string
     */
    private $uid;

    /**
     * @param string $uid
     */
    public function __construct(string $uid)
    {
        $this->uid = $uid;

        parent::__construct(\sprintf(
            'Sync WaitGroup: entity "%s" not found',
            $this->getUid()
        ));
    }

    /**
     * @return string
     */
    public function getUid(): string
    {
        return $this->uid;
    }
}
