<?php namespace Tests\Cache;

use Framework\Cache\Memcached;

class MemcachedTest extends TestCase
{
	public function setUp() : void
	{
		$this->configs = [
			[
				'host' => \getenv('MEMCACHED_HOST'),
			],
		];
		$this->cache = new Memcached($this->configs, $this->prefix, $this->serializer);
	}
}
