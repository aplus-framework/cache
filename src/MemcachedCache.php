<?php namespace Framework\Cache;

use OutOfBoundsException;

/**
 * Class MemcachedCache.
 */
class MemcachedCache extends Cache
{
	protected \Memcached $memcached;
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
	];

	public function __construct(
		array $configs = [],
		string $prefix = null,
		string $serializer = 'php'
	) {
		parent::__construct($configs, $prefix, $serializer);
		$this->validateConfigs();
		$this->connect();
	}

	public function __destruct()
	{
		$this->memcached->quit();
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
		$key = $this->memcached->get($this->renderKey($key));
		return $key !== false ? $key : null;
	}

	public function set(string $key, mixed $value, int $ttl = 60) : bool
	{
		return $this->memcached->set($this->renderKey($key), $value, $ttl);
	}

	public function delete(string $key) : bool
	{
		return $this->memcached->delete($this->renderKey($key));
	}

	public function flush() : bool
	{
		return $this->memcached->flush();
	}

	protected function connect() : void
	{
		$serializer = match ($this->serializer) {
			static::SERIALIZER_IGBINARY => \Memcached::SERIALIZER_IGBINARY,
			static::SERIALIZER_JSON => \Memcached::SERIALIZER_JSON,
			static::SERIALIZER_JSON_ARRAY => \Memcached::SERIALIZER_JSON_ARRAY,
			static::SERIALIZER_MSGPACK => \Memcached::SERIALIZER_MSGPACK,
			default => \Memcached::SERIALIZER_PHP,
		};
		$this->memcached = new \Memcached();
		$this->memcached->setOptions([
			\Memcached::OPT_BINARY_PROTOCOL => true,
			\Memcached::OPT_CONNECT_TIMEOUT => 100,
			\Memcached::OPT_COMPRESSION => true,
			\Memcached::OPT_DISTRIBUTION => \Memcached::DISTRIBUTION_CONSISTENT,
			\Memcached::OPT_LIBKETAMA_COMPATIBLE => true,
			\Memcached::OPT_POLL_TIMEOUT => 100,
			\Memcached::OPT_RECV_TIMEOUT => 100,
			\Memcached::OPT_REMOVE_FAILED_SERVERS => true,
			\Memcached::OPT_RETRY_TIMEOUT => 1,
			\Memcached::OPT_SEND_TIMEOUT => 100,
			\Memcached::OPT_SERIALIZER => $serializer,
			\Memcached::OPT_SERVER_FAILURE_LIMIT => 2,
		]);
		foreach ($this->configs['servers'] as $server) {
			$this->memcached->addServer(
				$server['host'],
				$server['port'] ?? 11211,
				$server['weight'] ?? 0
			);
		}
	}
}
