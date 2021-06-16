<?php namespace Tests\Cache;

use Framework\Cache\RedisCache;

class RedisCacheTest extends TestCase
{
	public function setUp() : void
	{
		$this->configs = [
			'host' => \getenv('REDIS_HOST'),
		];
		$this->cache = new RedisCache($this->configs, $this->prefix, $this->serializer);
	}
}
