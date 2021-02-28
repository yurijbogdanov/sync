<?php
declare(strict_types=1);

namespace Sync;

use InvalidArgumentException;

/**
 * Class Dsn.
 */
final class Dsn
{
    /**
     * @var string
     */
    private $scheme;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string|null
     */
    private $user;

    /**
     * @var string|null
     */
    private $password;

    /**
     * @var int|null
     */
    private $port;

    /**
     * @var string|null
     */
    private $path;

    /**
     * @var array
     */
    private $options;

    /**
     * @var string
     */
    private $dsn;

    /**
     * @param string $scheme
     * @param string $host
     * @param string|null $user
     * @param string|null $password
     * @param int|null $port
     * @param string|null $path
     * @param array $options
     */
    public function __construct(string $scheme, string $host, ?string $user = null, ?string $password = null, ?int $port = null, ?string $path = null, array $options = [])
    {
        $this->scheme = $scheme;
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->port = $port;
        $this->path = $path;
        $this->options = $options;
    }

    /**
     * @param string $dsn
     *
     * @return self
     */
    public static function createFromString(string $dsn): self
    {
        if (false === $parsedDsn = parse_url($dsn)) {
            throw new InvalidArgumentException(sprintf('The "%s" DSN is invalid.', $dsn));
        }

        if (!isset($parsedDsn['scheme'])) {
            throw new InvalidArgumentException(sprintf('The "%s" DSN must contain a scheme.', $dsn));
        }

        if (!isset($parsedDsn['host'])) {
            throw new InvalidArgumentException(sprintf('The "%s" DSN must contain a host.', $dsn));
        }

        $user = '' !== ($parsedDsn['user'] ?? '') ? urldecode($parsedDsn['user']) : null;
        $password = '' !== ($parsedDsn['pass'] ?? '') ? urldecode($parsedDsn['pass']) : null;
        $port = $parsedDsn['port'] ?? null;
        $path = $parsedDsn['path'] ?? null;
        parse_str($parsedDsn['query'] ?? '', $query);

        $dsnObject = new self($parsedDsn['scheme'], $parsedDsn['host'], $user, $password, $port, $path, $query);
        $dsnObject->dsn = $dsn;

        return $dsnObject;
    }

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return string|null
     */
    public function getUser(): ?string
    {
        return $this->user;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @return int|null
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getOption(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * @return string
     */
    public function getOriginalDsn(): string
    {
        return $this->dsn;
    }
}
