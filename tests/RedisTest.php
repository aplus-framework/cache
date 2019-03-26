<?php namespace Tests\Cache;

use Framework\Cache\Redis;

class RedisTest extends TestCase
{
	public function setUp()
	{
		$this->configs = [
			'host' => \getenv('GITLAB_CI') ? 'redis' : '127.0.0.1',
		];
		$this->cache = new Redis($this->configs, $this->prefix, $this->serializer);
	}
}
