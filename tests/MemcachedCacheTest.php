<?php namespace Tests\Cache;

use Framework\Cache\MemcachedCache;

class MemcachedCacheTest extends TestCase
{
	public function setUp() : void
	{
		$this->configs = [
			'servers' => [
				[
					'host' => \getenv('MEMCACHED_HOST'),
				],
			],
		];
		$this->cache = new MemcachedCache($this->configs, $this->prefix, $this->serializer);
	}
}
