<?php namespace Framework\Cache;

/**
 * Class Cache.
 */
abstract class Cache
{
	/**
	 * Driver specific configurations.
	 *
	 * @var array
	 */
	protected $configs = [];
	/**
	 * Keys prefix.
	 *
	 * @var string|null
	 */
	protected $prefix;
	/**
	 * Data serializer.
	 *
	 * @var string
	 */
	protected $serializer = 'php';

	/**
	 * Cache constructor.
	 *
	 * @param array       $configs    Driver specific configurations
	 * @param string|null $prefix     Keys prefix
	 * @param string      $serializer Data serializer
	 */
	public function __construct(array $configs, string $prefix = null, string $serializer = 'php')
	{
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
	 * @return mixed|null The item value or null if not found
	 */
	abstract public function get(string $key);

	public function getMulti(array $keys) : array
	{
		$keys = \array_fill_keys($keys, false);
		foreach ($keys as $key => &$value) {
			$value = $this->get($key);
		}
		return $keys;
	}

	/**
	 * Sets one item to the cache storage.
	 *
	 * @param string $key   The item name
	 * @param mixed  $value The item value
	 * @param int    $ttl   The Time To Live for the item
	 *
	 * @return bool TRUE if the item was set, FALSE if fail to set
	 */
	abstract public function set(string $key, $value, int $ttl = 60) : bool;

	public function setMulti(array $keys, int $ttl = 60) : array
	{
		foreach ($keys as $key => &$value) {
			$value = $this->set($key, $value, $ttl);
		}
		return $keys;
	}

	/**
	 * Deletes one item from the cache storage.
	 *
	 * @param string $key the item name
	 *
	 * @return bool TRUE if the item was deleted, FALSE if fail to delete
	 */
	abstract public function delete(string $key) : bool;

	public function deleteMulti(array $keys) : array
	{
		$keys = \array_fill_keys($keys, false);
		foreach ($keys as $key => &$value) {
			$value = $this->delete($key);
		}
		return $keys;
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
	 * @param string $key    The item name
	 * @param int    $offset The value to increment
	 * @param int    $ttl    The Time To Live for the item
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
	 * @param string $key    The item name
	 * @param int    $offset The value to decrement
	 * @param int    $ttl    The Time To Live for the item
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

	protected function setSerializer(string $serializer)
	{
		if ( ! \in_array($serializer, ['igbinary', 'json', 'php'])) {
			throw new \InvalidArgumentException("Invalid serializer: {$serializer}");
		}
		$this->serializer = $serializer;
	}

	protected function renderKey(string $key) : string
	{
		return $this->prefix . $key;
	}

	protected function serialize($value) : string
	{
		if ($this->serializer === 'igbinary') {
			return \igbinary_serialize($value);
		}
		if ($this->serializer === 'json') {
			return \json_encode($value) ?: '';
		}
		return \serialize($value);
	}

	protected function unserialize(string $value)
	{
		if ($this->serializer === 'igbinary') {
			return \igbinary_unserialize($value);
		}
		if ($this->serializer === 'json') {
			return \json_decode($value, true);
		}
		return \unserialize($value, [false]);
	}
}
