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

use Framework\Log\LogLevel;
use Memcached;
use OutOfBoundsException;
use RuntimeException;

/**
 * Class MemcachedCache.
 *
 * @package cache
 */
class MemcachedCache extends Cache
{
    protected Memcached $memcached;
    /**
     * Memcached Cache handler configurations.
     *
     * @var array<string,mixed>
     */
    protected array $configs = [
        'servers' => [
            [
                'host' => '127.0.0.1',
                'port' => 11211,
                'weight' => 0,
            ],
        ],
        'options' => [
            Memcached::OPT_BINARY_PROTOCOL => true,
        ],
    ];

    public function __destruct()
    {
        $this->memcached->quit();
    }

    protected function initialize() : void
    {
        $this->validateConfigs();
        $this->connect();
    }

    protected function validateConfigs() : void
    {
        foreach ($this->configs['servers'] as $index => $config) {
            if (empty($config['host'])) {
                throw new OutOfBoundsException(
                    "Memcached host config empty on server '{$index}'"
                );
            }
        }
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
        $key = $this->memcached->get($this->renderKey($key));
        return $key === false && $this->memcached->getResultCode() === Memcached::RES_NOTFOUND
            ? null
            : $key;
    }

    public function set(string $key, mixed $value, int $ttl = null) : bool
    {
        if (isset($this->debugCollector)) {
            $start = \microtime(true);
            return $this->addDebugSet(
                $key,
                $ttl,
                $start,
                $value,
                $this->memcached->set($this->renderKey($key), $value, $this->makeTtl($ttl))
            );
        }
        return $this->memcached->set($this->renderKey($key), $value, $this->makeTtl($ttl));
    }

    public function delete(string $key) : bool
    {
        if (isset($this->debugCollector)) {
            $start = \microtime(true);
            return $this->addDebugDelete(
                $key,
                $start,
                $this->memcached->delete($this->renderKey($key))
            );
        }
        return $this->memcached->delete($this->renderKey($key));
    }

    public function flush() : bool
    {
        if (isset($this->debugCollector)) {
            $start = \microtime(true);
            return $this->addDebugFlush(
                $start,
                $this->memcached->flush()
            );
        }
        return $this->memcached->flush();
    }

    protected function connect() : void
    {
        $this->configs['options'][Memcached::OPT_SERIALIZER] = match ($this->serializer) {
            Serializer::IGBINARY => Memcached::SERIALIZER_IGBINARY,
            Serializer::JSON => Memcached::SERIALIZER_JSON,
            Serializer::JSON_ARRAY => Memcached::SERIALIZER_JSON_ARRAY,
            Serializer::MSGPACK => Memcached::SERIALIZER_MSGPACK,
            default => Memcached::SERIALIZER_PHP,
        };
        $this->memcached = new Memcached();
        $pool = [];
        foreach ($this->configs['servers'] as $server) {
            $host = $server['host'] . ':' . ($server['port'] ?? 11211);
            if (\in_array($host, $pool, true)) {
                $this->log(
                    'Cache (memcached): Server pool already has ' . $host,
                    LogLevel::DEBUG
                );
                continue;
            }
            $result = $this->memcached->addServer(
                $server['host'],
                $server['port'] ?? 11211,
                $server['weight'] ?? 0
            );
            if ($result === false) {
                $this->log("Cache (memcached): Could not add {$host} to server pool");
            }
            $pool[] = $host;
        }
        $result = $this->memcached->setOptions($this->configs['options']);
        if ($result === false) {
            $this->log('Cache (memcached): ' . $this->memcached->getLastErrorMessage());
        }
        if ( ! $this->memcached->getStats()) {
            throw new RuntimeException('Cache (memcached): Could not connect to any server');
        }
    }
}
