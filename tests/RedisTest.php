<?php namespace Tests\Cache;

use Framework\Cache\Redis;

class RedisTest extends TestCase
{
	public function setUp()
	{
		$this->configs = [
			'host' => \getenv('REDIS_HOST'),
		];
		$this->cache = new Redis($this->configs, $this->prefix, $this->serializer);
	}
}
