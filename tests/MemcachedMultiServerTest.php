<?php namespace Tests\Cache;

class MemcachedMultiServerTest extends MemcachedTest
{
	public function setUp()
	{
		$this->configs = [
			[
				'host' => \getenv('GITLAB_CI') ? 'memcached' : '127.0.0.1',
			],
			[
				'host' => \getenv('GITLAB_CI') ? 'memcached' : '127.0.0.1',
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
