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

use Framework\Cache\Debug\CacheCollector;
use Framework\Debug\Debugger;
use Framework\Log\Logger;
use Framework\Log\LogLevel;
use JetBrains\PhpStorm\Pure;

/**
 * Class Cache.
 *
 * @package cache
 */
abstract class Cache
{
    /**
     * Driver specific configurations.
     *
     * @var array<string,mixed>
     */
    protected array $configs = [];
    /**
     * Keys prefix.
     *
     * @var string|null
     */
    protected ?string $prefix;
    /**
     * Data serializer.
     *
     * @var Serializer
     */
    protected Serializer $serializer;
    /**
     * The Logger instance if is set.
     *
     * @var Logger|null
     */
    protected ?Logger $logger;
    /**
     * The default Time To Live value.
     *
     * Used when set methods has the $ttl param as null.
     *
     * @var int
     */
    public int $defaultTtl = 60;
    protected CacheCollector $debugCollector;

    /**
     * Cache constructor.
     *
     * @param array<string,mixed> $configs Driver specific configurations
     * @param string|null $prefix Keys prefix
     * @param Serializer $serializer Data serializer
     */
    public function __construct(
        array $configs = [],
        string $prefix = null,
        Serializer $serializer = Serializer::PHP,
        Logger $logger = null
    ) {
        if ($configs) {
            $this->configs = \array_replace_recursive($this->configs, $configs);
        }
        $this->prefix = $prefix;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->initialize();
    }

    /**
     * Initialize Cache handlers and configurations.
     */
    protected function initialize() : void
    {
    }

    protected function log(
        string $message,
        LogLevel $level = LogLevel::ERROR
    ) : void {
        if (isset($this->logger)) {
            $this->logger->log($level, $message);
        }
    }

    /**
     * Make the Time To Live value.
     *
     * @param int|null $seconds TTL value or null to use the default
     *
     * @return int The input $seconds or the $defaultTtl as integer
     */
    #[Pure]
    protected function makeTtl(?int $seconds) : int
    {
        return $seconds ?? $this->defaultTtl;
    }

    /**
     * Gets one item from the cache storage.
     *
     * @param string $key The item name
     *
     * @return mixed The item value or null if not found
     */
    abstract public function get(string $key) : mixed;

    /**
     * Gets multi items from the cache storage.
     *
     * @param array<int,string> $keys List of items names to get
     *
     * @return array<string,mixed> associative array with key names and respective values
     */
    public function getMulti(array $keys) : array
    {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $this->get($key);
        }
        return $values;
    }

    /**
     * Sets one item to the cache storage.
     *
     * @param string $key The item name
     * @param mixed $value The item value
     * @param int|null $ttl The Time To Live for the item or null to use the default
     *
     * @return bool TRUE if the item was set, FALSE if fail to set
     */
    abstract public function set(string $key, mixed $value, int $ttl = null) : bool;

    /**
     * Sets multi items to the cache storage.
     *
     * @param array<string,mixed> $data Associative array with key names and respective values
     * @param int|null $ttl The Time To Live for all the items or null to use the default
     *
     * @return array<string,bool> associative array with key names and respective set status
     */
    public function setMulti(array $data, int $ttl = null) : array
    {
        foreach ($data as $key => &$value) {
            $value = $this->set($key, $value, $ttl);
        }
        return $data;
    }

    /**
     * Deletes one item from the cache storage.
     *
     * @param string $key the item name
     *
     * @return bool TRUE if the item was deleted, FALSE if fail to delete
     */
    abstract public function delete(string $key) : bool;

    /**
     * Deletes multi items from the cache storage.
     *
     * @param array<int,string> $keys List of items names to be deleted
     *
     * @return array<string,bool> associative array with key names and respective delete status
     */
    public function deleteMulti(array $keys) : array
    {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $this->delete($key);
        }
        return $values;
    }

    /**
     * Flush the cache storage.
     *
     * @return bool TRUE if all items are deleted, otherwise FALSE
     */
    abstract public function flush() : bool;

    /**
     * Increments the value of one item.
     *
     * @param string $key The item name
     * @param int $offset The value to increment
     * @param int|null $ttl The Time To Live for the item or null to use the default
     *
     * @return int The current item value
     */
    public function increment(string $key, int $offset = 1, int $ttl = null) : int
    {
        $offset = (int) \abs($offset);
        $value = (int) $this->get($key);
        $value = $value ? $value + $offset : $offset;
        $this->set($key, $value, $ttl);
        return $value;
    }

    /**
     * Decrements the value of one item.
     *
     * @param string $key The item name
     * @param int $offset The value to decrement
     * @param int|null $ttl The Time To Live for the item or null to use the default
     *
     * @return int The current item value
     */
    public function decrement(string $key, int $offset = 1, int $ttl = null) : int
    {
        $offset = (int) \abs($offset);
        $value = (int) $this->get($key);
        $value = $value ? $value - $offset : -$offset;
        $this->set($key, $value, $ttl);
        return $value;
    }

    #[Pure]
    protected function renderKey(string $key) : string
    {
        return $this->prefix . $key;
    }

    /**
     * @param mixed $value
     *
     * @throws \JsonException
     *
     * @return string
     */
    protected function serialize(mixed $value) : string
    {
        if ($this->serializer === Serializer::IGBINARY) {
            return \igbinary_serialize($value);
        }
        if ($this->serializer === Serializer::JSON
            || $this->serializer === Serializer::JSON_ARRAY
        ) {
            return \json_encode($value, \JSON_THROW_ON_ERROR);
        }
        if ($this->serializer === Serializer::MSGPACK) {
            return \msgpack_serialize($value);
        }
        return \serialize($value);
    }

    /**
     * @param string $value
     *
     * @return mixed
     */
    protected function unserialize(string $value) : mixed
    {
        if ($this->serializer === Serializer::IGBINARY) {
            return @\igbinary_unserialize($value);
        }
        if ($this->serializer === Serializer::JSON) {
            return \json_decode($value);
        }
        if ($this->serializer === Serializer::JSON_ARRAY) {
            return \json_decode($value, true);
        }
        if ($this->serializer === Serializer::MSGPACK) {
            return \msgpack_unserialize($value);
        }
        return \unserialize($value, ['allowed_classes' => true]);
    }

    public function setDebugCollector(CacheCollector $debugCollector) : static
    {
        $this->debugCollector = $debugCollector;
        $this->debugCollector->setInfo([
            'class' => static::class,
            'serializer' => $this->serializer->value,
        ]);
        return $this;
    }

    protected function addDebugGet(string $key, float $start, mixed $value) : mixed
    {
        $end = \microtime(true);
        $this->debugCollector->addData([
            'start' => $start,
            'end' => $end,
            'command' => 'GET',
            'status' => $value === null ? 'FAIL' : 'OK',
            'key' => $key,
            'value' => Debugger::makeDebugValue($value),
        ]);
        return $value;
    }

    protected function addDebugSet(string $key, ?int $ttl, float $start, mixed $value, bool $status) : bool
    {
        $end = \microtime(true);
        $this->debugCollector->addData([
            'start' => $start,
            'end' => $end,
            'command' => 'SET',
            'status' => $status ? 'OK' : 'FAIL',
            'key' => $key,
            'value' => Debugger::makeDebugValue($value),
            'ttl' => $this->makeTtl($ttl),
        ]);
        return $status;
    }

    protected function addDebugDelete(string $key, float $start, bool $status) : bool
    {
        $end = \microtime(true);
        $this->debugCollector->addData([
            'start' => $start,
            'end' => $end,
            'command' => 'DELETE',
            'status' => $status ? 'OK' : 'FAIL',
            'key' => $key,
        ]);
        return $status;
    }

    protected function addDebugFlush(float $start, bool $status) : bool
    {
        $end = \microtime(true);
        $this->debugCollector->addData([
            'start' => $start,
            'end' => $end,
            'command' => 'FLUSH',
            'status' => $status ? 'OK' : 'FAIL',
        ]);
        return $status;
    }
}
