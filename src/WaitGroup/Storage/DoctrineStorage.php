<?php
declare(strict_types=1);

namespace Sync\WaitGroup\Storage;

use Sync\WaitGroup\Exception\WaitGroupStorageException;
use Doctrine\DBAL\Connection;
use PDO;
use Throwable;

/**
 * Class DoctrineStorage.
 */
final class DoctrineStorage implements StorageInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @param Connection $connection
     * @param array $options
     */
    public function __construct(Connection $connection, array $options = [])
    {
        $this->setTableName($options['table_name'] ?? 'sync_waitgroup');
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function insertWaitGroup(string $uid): void
    {
        try {
            $sql = <<<'SQL'
INSERT INTO `%s` (`uid`, `counter`, `created_at`, `updated_at`) 
VALUES (:uid, 0, NOW(), NOW());
SQL;
            $sql = sprintf($sql, $this->tableName);
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':uid', $uid, PDO::PARAM_STR);
            $stmt->execute();
        } catch (Throwable $e) {
            throw new WaitGroupStorageException($e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetchWaitGroup(string $uid): ?array
    {
        try {
            $sql = <<<'SQL'
SELECT * 
FROM `%s` 
WHERE `uid` = :uid 
LIMIT 1;
SQL;
            $sql = sprintf($sql, $this->tableName);
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':uid', $uid, PDO::PARAM_STR);
            $stmt->execute();
            $wg = $stmt->fetch(PDO::FETCH_ASSOC);

            return $wg ?: null;
        } catch (Throwable $e) {
            throw new WaitGroupStorageException($e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateCounter(string $uid, int $delta): void
    {
        try {
            $sql = <<<'SQL'
UPDATE `%s` 
SET `counter` = (`counter` + :delta), `updated_at` = NOW() 
WHERE `uid` = :uid 
LIMIT 1;
SQL;
            $sql = sprintf($sql, $this->tableName);
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':uid', $uid, PDO::PARAM_STR);
            $stmt->bindValue(':delta', $delta, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Throwable $e) {
            throw new WaitGroupStorageException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @param string $tableName
     *
     * @return void
     */
    private function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }
}
