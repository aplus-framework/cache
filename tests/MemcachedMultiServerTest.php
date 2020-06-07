<?php namespace Tests\Cache;

class MemcachedMultiServerTest extends MemcachedTest
{
	public function setUp() : void
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
		$this->expectException(\OutOfBoundsException::class);
		$this->expectErrorMessage('Memcached server host empty on config "server"');
		new \Framework\Cache\Memcached([
			'server' => [
				['foo'],
			],
		]);
	}
}
