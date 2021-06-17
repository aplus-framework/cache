<?php namespace Framework\Cache;

use Redis;

/**
 * Class RedisCache.
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
	];

	public function __destruct()
	{
		$this->redis->close();
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
	}

	public function get(string $key) : mixed
	{
		$value = $this->redis->get($this->renderKey($key));
		if ($value === false) {
			return null;
		}
		return $this->unserialize($value);
	}

	public function set(string $key, mixed $value, int $ttl = null) : bool
	{
		return $this->redis->set(
			$this->renderKey($key),
			$this->serialize($value),
			$this->makeTTL($ttl)
		);
	}

	public function delete(string $key) : bool
	{
		return (bool) $this->redis->del($this->renderKey($key));
	}

	public function flush() : bool
	{
		return $this->redis->flushAll();
	}
}
