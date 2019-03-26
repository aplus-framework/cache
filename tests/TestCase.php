<?php namespace Tests\Cache;

use Framework\Cache\Cache;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
	/**
	 * @var Cache
	 */
	protected $cache;
	/**
	 * @var array
	 */
	protected $configs = [];
	/**
	 * @var string
	 */
	protected $prefix = 'test';
	/**
	 * @var string
	 */
	protected $serializer = Cache::SERIALIZER_PHP;

	public function tearDown()
	{
		$this->cache->flush();
		$this->cache = null;
	}

	public function testSetAndGet()
	{
		$this->assertNull($this->cache->get('foo'));
		$this->assertTrue($this->cache->set('foo', 'bar', 1));
		$this->assertEquals('bar', $this->cache->get('foo'));
		\sleep(2);
		$this->assertNull($this->cache->get('foo'));
	}

	public function testSetMultiAndGetMulti()
	{
		$this->assertEquals(
			['foo' => null, 'bar' => null],
			$this->cache->getMulti(['foo', 'bar'])
		);
		$this->assertEquals(
			['foo' => true, 'bar' => true],
			$this->cache->setMulti(['foo' => 'x', 'bar' => 'y'], 1)
		);
		$this->assertEquals(
			['bar' => 'y', 'foo' => 'x', 'baz' => null],
			$this->cache->getMulti(['bar', 'foo', 'baz'])
		);
		\sleep(2);
		$this->assertEquals(
			['foo' => null, 'bar' => null],
			$this->cache->getMulti(['foo', 'bar'])
		);
	}

	public function testDelete()
	{
		$this->assertNull($this->cache->get('foo'));
		$this->assertTrue($this->cache->set('foo', 'bar', 1));
		$this->assertEquals('bar', $this->cache->get('foo'));
		$this->assertTrue($this->cache->delete('foo'));
		$this->assertNull($this->cache->get('foo'));
	}

	public function testDeleteMulti()
	{
		$this->assertEquals(
			['foo' => null, 'bar' => null],
			$this->cache->getMulti(['foo', 'bar'])
		);
		$this->assertEquals(
			['foo' => true, 'bar' => true],
			$this->cache->setMulti(['foo' => 'x', 'bar' => 'y'], 1)
		);
		$this->assertEquals(
			['bar' => 'y', 'foo' => 'x'],
			$this->cache->getMulti(['bar', 'foo'])
		);
		$this->assertEquals(
			['foo' => true, 'bar' => true],
			$this->cache->deleteMulti(['foo', 'bar'])
		);
		$this->assertEquals(
			['foo' => null, 'bar' => null],
			$this->cache->getMulti(['foo', 'bar'])
		);
	}

	public function testFlush()
	{
		$this->assertEquals(
			['foo' => true, 'bar' => true],
			$this->cache->setMulti(['foo' => 'x', 'bar' => 'y'], 1)
		);
		$this->assertEquals(
			['bar' => 'y', 'foo' => 'x'],
			$this->cache->getMulti(['bar', 'foo'])
		);
		$this->assertTrue($this->cache->flush());
		$this->assertEquals(
			['bar' => null, 'foo' => null],
			$this->cache->getMulti(['bar', 'foo'])
		);
	}

	public function testIncrement()
	{
		$this->assertEquals(1, $this->cache->increment('i'));
		$this->assertEquals(2, $this->cache->increment('i'));
		$this->assertEquals(5, $this->cache->increment('i', 3));
		$this->assertEquals(6, $this->cache->increment('i', 1, 2));
		\sleep(3);
		$this->assertEquals(1, $this->cache->increment('i'));
		$this->assertEquals(11, $this->cache->increment('i', 10));
	}

	public function testDecrement()
	{
		$this->assertEquals(-1, $this->cache->decrement('i'));
		$this->assertEquals(-2, $this->cache->decrement('i'));
		$this->assertEquals(-5, $this->cache->decrement('i', 3));
		$this->assertEquals(-6, $this->cache->decrement('i', 1, 2));
		\sleep(3);
		$this->assertEquals(-1, $this->cache->decrement('i'));
		$this->assertEquals(-11, $this->cache->decrement('i', 10));
	}

	public function testIncrementAndDecrement()
	{
		$this->assertEquals(1, $this->cache->increment('id'));
		$this->assertEquals(2, $this->cache->increment('id'));
		$this->assertEquals(3, $this->cache->increment('id'));
		$this->assertEquals(2, $this->cache->decrement('id'));
		$this->assertEquals(0, $this->cache->decrement('id', 2));
	}
}
