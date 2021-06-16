<?php namespace Tests\Cache;

use Framework\Cache\MemcachedCache;

final class MemcachedCacheMultiServerTest extends MemcachedCacheTest
{
	public function setUp() : void
	{
		$this->configs = [
			'servers' => [
				[
					'host' => \getenv('MEMCACHED_HOST'),
				],
				[
					'host' => \getenv('MEMCACHED_HOST'),
				],
			],
		];
		parent::setUp();
	}

	public function testMultiServerEmptyHost() : void
	{
		$this->expectException(\OutOfBoundsException::class);
		$this->expectErrorMessage("Memcached host config empty on server '0'");
		new MemcachedCache([
			'servers' => [
				[
					'host' => '',
				],
			],
		]);
	}
}
