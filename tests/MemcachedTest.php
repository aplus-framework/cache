<?php namespace Tests\Cache;

use Framework\Cache\Memcached;

class MemcachedTest extends TestCase
{
	public function setUp()
	{
		$this->configs = [
			[
				'host' => \getenv('GITLAB_CI') ? 'memcached' : '127.0.0.1',
			],
		];
		$this->cache = new Memcached($this->configs, $this->prefix, $this->serializer);
	}
}
