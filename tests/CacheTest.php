<?php namespace Tests\Sample;

use Framework\Cache\Cache;
use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase
{
	/**
	 * @var Cache
	 */
	protected $cache;

	public function setup()
	{
		$this->cache = new Cache();
	}

	public function testSample()
	{
		$this->assertEquals(
			'Framework\Cache\Cache::test',
			$this->cache->test()
		);
	}
}
