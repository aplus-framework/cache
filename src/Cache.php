<?php namespace Framework\Cache;

use InvalidArgumentException;

/**
 * Class Cache.
 */
abstract class Cache
{
	/**
	 * The Igbinary serializer.
	 */
	public const SERIALIZER_IGBINARY = 'igbinary';
	/**
	 * The JSON serializer.
	 */
	public const SERIALIZER_JSON = 'json';
	/**
	 * The JSON Array serializer.
	 */
	public const SERIALIZER_JSON_ARRAY = 'json-array';
	/**
	 * The MessagePack serializer.
	 */
	public const SERIALIZER_MSGPACK = 'msgpack';
	/**
	 * The PHP serializer.
	 */
	public const SERIALIZER_PHP = 'php';
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
	 * @var string
	 */
	protected string $serializer;

	/**
	 * Cache constructor.
	 *
	 * @param array<string,mixed> $configs Driver specific configurations
	 * @param string|null $prefix Keys prefix
	 * @param string $serializer Data serializer. One of the SERIALIZER_* constants
	 */
	public function __construct(
		array $configs,
		string $prefix = null,
		string $serializer = Cache::SERIALIZER_PHP
	) {
		if ($configs) {
			$this->configs = \array_replace_recursive($this->configs, $configs);
		}
		$this->prefix = $prefix;
		$this->setSerializer($serializer);
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
	 * @param int $ttl The Time To Live for the item
	 *
	 * @return bool TRUE if the item was set, FALSE if fail to set
	 */
	abstract public function set(string $key, mixed $value, int $ttl = 60) : bool;

	/**
	 * Sets multi items to the cache storage.
	 *
	 * @param array<string,mixed> $data Associative array with key names and respective values
	 * @param int $ttl The Time To Live for all the items
	 *
	 * @return array<string,bool> associative array with key names and respective set status
	 */
	public function setMulti(array $data, int $ttl = 60) : array
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
	 * @param int $ttl The Time To Live for the item
	 *
	 * @return int The current item value
	 */
	public function increment(string $key, int $offset = 1, int $ttl = 60) : int
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
	 * @param int $ttl The Time To Live for the item
	 *
	 * @return int The current item value
	 */
	public function decrement(string $key, int $offset = 1, int $ttl = 60) : int
	{
		$offset = (int) \abs($offset);
		$value = (int) $this->get($key);
		$value = $value ? $value - $offset : -$offset;
		$this->set($key, $value, $ttl);
		return $value;
	}

	protected function setSerializer(string $serializer) : void
	{
		if ( ! \in_array($serializer, [
			static::SERIALIZER_IGBINARY,
			static::SERIALIZER_JSON,
			static::SERIALIZER_JSON_ARRAY,
			static::SERIALIZER_MSGPACK,
			static::SERIALIZER_PHP,
		], true)) {
			throw new InvalidArgumentException("Invalid serializer: {$serializer}");
		}
		$this->serializer = $serializer;
	}

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
		if ($this->serializer === static::SERIALIZER_IGBINARY) {
			return \igbinary_serialize($value);
		}
		if ($this->serializer === static::SERIALIZER_JSON
			|| $this->serializer === static::SERIALIZER_JSON_ARRAY
		) {
			return \json_encode($value, \JSON_THROW_ON_ERROR);
		}
		if ($this->serializer === static::SERIALIZER_MSGPACK) {
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
		if ($this->serializer === static::SERIALIZER_IGBINARY) {
			return @\igbinary_unserialize($value);
		}
		if ($this->serializer === static::SERIALIZER_JSON) {
			return \json_decode($value);
		}
		if ($this->serializer === static::SERIALIZER_JSON_ARRAY) {
			return \json_decode($value, true);
		}
		if ($this->serializer === static::SERIALIZER_MSGPACK) {
			return \msgpack_unserialize($value);
		}
		return \unserialize($value, ['allowed_classes' => true]);
	}
}
