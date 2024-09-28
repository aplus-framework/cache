<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework Cache Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Framework\Cache;

use Framework\Log\Logger;
use Override;
use Redis;
use SensitiveParameter;

/**
 * Class RedisCache.
 *
 * @package cache
 */
class RedisCache extends Cache
{
    protected Redis $redis;
    /**
     * Redis Cache handler configurations.
     *
     * @var array<string,mixed>
     */
    protected array $configs = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'timeout' => 0.0,
        'password' => null,
        'database' => null,
    ];

    /**
     * RedisCache constructor.
     *
     * @param Redis|array<string,mixed>|null $configs Driver specific
     * configurations. Set null to not initialize or a custom Redis object.
     * @param string|null $prefix Keys prefix
     * @param Serializer|string $serializer Data serializer
     * @param Logger|null $logger Logger instance
     */
    public function __construct(
        #[SensitiveParameter]
        Redis | array | null $configs = [],
        ?string $prefix = null,
        Serializer | string $serializer = Serializer::PHP,
        ?Logger $logger = null
    ) {
        parent::__construct($configs, $prefix, $serializer, $logger);
        if ($configs instanceof Redis) {
            $this->setRedis($configs);
            $this->setAutoClose(false);
        }
    }

    protected function initialize() : void
    {
        $this->connect();
    }

    protected function connect() : void
    {
        $this->redis = new Redis();
        $this->redis->connect(
            $this->configs['host'],
            $this->configs['port'],
            $this->configs['timeout']
        );
        if (isset($this->configs['password'])) {
            $this->redis->auth($this->configs['password']);
        }
        if (isset($this->configs['database'])) {
            $this->redis->select($this->configs['database']);
        }
    }

    /**
     * Set custom Redis instance.
     *
     * @since 3.2
     *
     * @param Redis $redis
     *
     * @return static
     */
    public function setRedis(Redis $redis) : static
    {
        $this->redis = $redis;
        return $this;
    }

    /**
     * Get Redis instance or null.
     *
     * @since 3.2
     *
     * @return Redis|null
     */
    public function getRedis() : ?Redis
    {
        return $this->redis ?? null;
    }

    public function get(string $key) : mixed
    {
        if (isset($this->debugCollector)) {
            $start = \microtime(true);
            return $this->addDebugGet(
                $key,
                $start,
                $this->getValue($key)
            );
        }
        return $this->getValue($key);
    }

    protected function getValue(string $key) : mixed
    {
        $value = $this->redis->get($this->renderKey($key));
        if ($value === false) {
            return null;
        }
        return $this->unserialize($value);
    }

    public function set(string $key, mixed $value, ?int $ttl = null) : bool
    {
        if (isset($this->debugCollector)) {
            $start = \microtime(true);
            return $this->addDebugSet(
                $key,
                $ttl,
                $start,
                $value,
                $this->redis->set(
                    $this->renderKey($key),
                    $this->serialize($value),
                    $this->makeTtl($ttl)
                )
            );
        }
        return $this->redis->set(
            $this->renderKey($key),
            $this->serialize($value),
            $this->makeTtl($ttl)
        );
    }

    public function delete(string $key) : bool
    {
        if (isset($this->debugCollector)) {
            $start = \microtime(true);
            return $this->addDebugDelete(
                $key,
                $start,
                (bool) $this->redis->del($this->renderKey($key))
            );
        }
        return (bool) $this->redis->del($this->renderKey($key));
    }

    public function flush() : bool
    {
        if (isset($this->debugCollector)) {
            $start = \microtime(true);
            return $this->addDebugFlush(
                $start,
                $this->redis->flushAll()
            );
        }
        return $this->redis->flushAll();
    }

    #[Override]
    public function close() : bool
    {
        return $this->redis->close();
    }
}
