<?php namespace Framework\Cache;

use OutOfBoundsException;

/**
 * Class Memcached.
 */
class Memcached extends Cache
{
	protected \Memcached $memcached;
	protected array $configs = [
		[
			'host' => '127.0.0.1',
			'port' => 11211,
			'weight' => 1,
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
		foreach ($this->configs as $index => $config) {
			if (empty($config['host'])) {
				throw new OutOfBoundsException(
					"Memcached server host empty on config \"{$index}\""
				);
			}
		}
	}

	public function get(string $key)
	{
		$key = $this->memcached->get($this->renderKey($key));
		return $key !== false ? $key : null;
	}

	public function set(string $key, $value, int $ttl = 60) : bool
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
		switch ($this->serializer) {
			case static::SERIALIZER_IGBINARY:
				$serializer = \Memcached::SERIALIZER_IGBINARY;
				break;
			case static::SERIALIZER_JSON:
				$serializer = \Memcached::SERIALIZER_JSON;
				break;
			case static::SERIALIZER_JSON_ARRAY:
				$serializer = \Memcached::SERIALIZER_JSON_ARRAY;
				break;
			case static::SERIALIZER_MSGPACK:
				$serializer = \Memcached::SERIALIZER_MSGPACK;
				break;
			case static::SERIALIZER_PHP:
			default:
				$serializer = \Memcached::SERIALIZER_PHP;
				break;
		}
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
		foreach ($this->configs as $configs) {
			$this->memcached->addServer(
				$configs['host'],
				$configs['port'] ?? 11211,
				$configs['weight'] ?? 1
			);
		}
	}
}
