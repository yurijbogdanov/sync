<?php
declare(strict_types=1);

namespace Sync\WaitGroup\Storage;

use Sync\Dsn;
use Sync\WaitGroup\Exception\WaitGroupStorageException;
use PDO;
use Throwable;

/**
 * Class PdoStorage.
 */
final class PdoStorage implements StorageInterface
{
    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $pdoDsn;

    /**
     * @var string
     */
    private $pdoUser;

    /**
     * @var string
     */
    private $pdoPassword;

    /**
     * @var array
     */
    private $pdoOptions = [];

    /**
     * @var string
     */
    private $tableName;

    /**
     * @param string $dsn
     * @param array $options
     *
     * @throws WaitGroupStorageException
     */
    public function __construct(string $dsn, array $options = [])
    {
        $this->setTableName($options['table_name'] ?? 'sync_waitgroup');
        $this->buildPdoDsn($dsn, $options);
        $this->connect();
    }

    /**
     * {@inheritdoc}
     */
    public function insertWaitGroup(string $uuid): void
    {
        try {
            $sql = <<<'SQL'
INSERT INTO `%s` (`uid`, `counter`, `created_at`, `updated_at`) 
VALUES (:uid, 0, NOW(), NOW());
SQL;
            $sql = sprintf($sql, $this->tableName);
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':uid', $uuid, PDO::PARAM_STR);
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
            $stmt = $this->pdo->prepare($sql);
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
    public function updateCounter(string $uuid, int $delta): void
    {
        try {
            $sql = <<<'SQL'
UPDATE `%s` 
SET `counter` = (`counter` + :delta), `updated_at` = NOW() 
WHERE `uid` = :uid 
LIMIT 1;
SQL;
            $sql = sprintf($sql, $this->tableName);
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':uid', $uuid, PDO::PARAM_STR);
            $stmt->bindValue(':delta', $delta, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Throwable $e) {
            throw new WaitGroupStorageException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @return void
     */
    private function connect(): void
    {
        try {
            $this->pdo = new PDO($this->pdoDsn, $this->pdoUser, $this->pdoPassword, $this->pdoOptions);
        } catch (Throwable $e) {
            throw new WaitGroupStorageException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @param string $dsn
     * @param array $options
     *
     * @return void
     */
    private function buildPdoDsn(string $dsn, array $options = []): void
    {
        // (pdo_)?sqlite3?:///... => (pdo_)?sqlite3?://localhost/... or else the URL will be invalid
        $dsn = preg_replace('#^((?:pdo_)?sqlite3?):///#', '$1://localhost/', $dsn);

        try {
            $dsnObject = Dsn::createFromString($dsn);
        } catch (Throwable $e) {
            throw new WaitGroupStorageException($e->getMessage(), 0, $e);
        }

        $driverAliasMap = [
            'postgres' => 'pgsql',
            'postgresql' => 'pgsql',
            'sqlite3' => 'sqlite',
        ];

        $scheme = $dsnObject->getScheme();
        $driver = $driverAliasMap[$scheme] ?? $scheme;

        // Doctrine DBAL supports passing its internal pdo_* driver names directly too (allowing both dashes and underscores).
        // This allows supporting the same here.
        if (0 === strpos($driver, 'pdo_') || 0 === strpos($driver, 'pdo-')) {
            $driver = substr($driver, 4);
        }

        switch ($driver) {
            case 'mysql':
            case 'pgsql':
                $pdoDsn = $driver . ':';

                $host = $dsnObject->getHost();
                if (null !== $host) {
                    $pdoDsn .= 'host=' . $host . ';';
                }

                $port = $dsnObject->getPort();
                if (null !== $port) {
                    $pdoDsn .= 'port=' . $port . ';';
                }

                $path = $dsnObject->getPath();
                if (null !== $path) {
                    $dbName = substr($path, 1); // Remove the leading slash
                    $pdoDsn .= 'dbname=' . $dbName . ';';
                }

                $this->pdoDsn = $pdoDsn;

                break;

            case 'sqlite':
                $path = $dsnObject->getPath();
                $path = substr((string)$path, 1); // Remove the leading slash
                $pdoDsn = 'sqlite:' . $path;

                $this->pdoDsn = $pdoDsn;

                break;

            default:
                throw new WaitGroupStorageException(sprintf(
                    'The scheme "%s" is not supported by the PdoStorage.',
                    $scheme
                ));
        }

        $this->pdoUser = $dsnObject->getUser();
        $this->pdoPassword = $dsnObject->getPassword();
        $this->pdoOptions = [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];
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
