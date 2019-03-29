<?php namespace Tests\Cache;

class MemcachedMultiServerTest extends MemcachedTest
{
	public function setUp()
	{
		$this->configs = [
			[
				'host' => \getenv('MEMCACHED_HOST'),
			],
			[
				'host' => \getenv('MEMCACHED_HOST'),
			],
		];
		parent::setUp();
	}

	public function testMutiServerEmptyHost()
	{
		$this->expectException(\Exception::class);
		(new \Framework\Cache\Memcached([
			'server' => [
				['foo'],
			],
		]));
	}
}
