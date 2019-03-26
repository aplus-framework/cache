<?php namespace Framework\Cache;

/**
 * Class Redis.
 */
class Redis extends Cache
{
	/**
	 * @var \Redis
	 */
	protected $redis;
	/**
	 * @var array
	 */
	protected $configs = [
		'host' => '127.0.0.1',
		'port' => 6379,
		'timeout' => 0.0,
	];

	public function __construct(
		array $configs = [],
		string $prefix = null,
		string $serializer = 'php'
	) {
		parent::__construct($configs, $prefix, $serializer);
		$this->connect();
	}

	public function __destruct()
	{
		if ($this->redis) {
			$this->redis->close();
		}
	}

	protected function connect()
	{
		$this->redis = new \Redis();
		$this->redis->connect(
			$this->configs['host'],
			$this->configs['port'],
			$this->configs['timeout']
		);
	}

	public function get(string $key)
	{
		$value = $this->redis->get($this->renderKey($key));
		if ($value === false) {
			return null;
		}
		return $this->unserialize($value);
	}

	public function set(string $key, $value, int $ttl = 60) : bool
	{
		return $this->redis->set(
			$this->renderKey($key),
			$this->serialize($value),
			$ttl
		);
	}

	public function delete(string $key) : bool
	{
		return $this->redis->del($this->renderKey($key));
	}

	public function flush() : bool
	{
		return $this->redis->flushAll();
	}
}
