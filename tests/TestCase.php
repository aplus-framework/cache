<?php
/*
 * This file is part of The Framework Cache Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Cache;

use Framework\Cache\Cache;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected ?Cache $cache;
    /**
     * @var array<string,mixed>
     */
    protected array $configs = [];
    protected string $prefix = 'test';
    protected string $serializer = Cache::SERIALIZER_PHP;

    public function tearDown() : void
    {
        $this->cache->flush();
        $this->cache = null;
    }

    public function testSetAndGet() : void
    {
        self::assertNull($this->cache->get('foo'));
        self::assertTrue($this->cache->set('foo', 'bar', 1));
        self::assertSame('bar', $this->cache->get('foo'));
        \sleep(2);
        self::assertNull($this->cache->get('foo'));
    }

    public function testSetAndGetNullAndFalseValues() : void
    {
        $this->assertNull($this->cache->get('null-value'));
        $this->assertNull($this->cache->get('false-value'));
        $this->cache->set('null-value', null);
        $this->cache->set('false-value', false);
        $this->assertNull($this->cache->get('null-value'));
        $this->assertFalse($this->cache->get('false-value'));
    }

    public function testSetMultiAndGetMulti() : void
    {
        self::assertSame(
            ['foo' => null, 'bar' => null],
            $this->cache->getMulti(['foo', 'bar'])
        );
        self::assertSame(
            ['foo' => true, 'bar' => true],
            $this->cache->setMulti(['foo' => 'x', 'bar' => 'y'], 1)
        );
        self::assertSame(
            ['bar' => 'y', 'foo' => 'x', 'baz' => null],
            $this->cache->getMulti(['bar', 'foo', 'baz'])
        );
        \sleep(2);
        self::assertSame(
            ['foo' => null, 'bar' => null],
            $this->cache->getMulti(['foo', 'bar'])
        );
    }

    public function testDelete() : void
    {
        self::assertNull($this->cache->get('foo'));
        self::assertTrue($this->cache->set('foo', 'bar', 1));
        self::assertSame('bar', $this->cache->get('foo'));
        self::assertTrue($this->cache->delete('foo'));
        self::assertNull($this->cache->get('foo'));
    }

    public function testDeleteMulti() : void
    {
        self::assertSame(
            ['foo' => null, 'bar' => null],
            $this->cache->getMulti(['foo', 'bar'])
        );
        self::assertSame(
            ['foo' => true, 'bar' => true],
            $this->cache->setMulti(['foo' => 'x', 'bar' => 'y'], 1)
        );
        self::assertSame(
            ['bar' => 'y', 'foo' => 'x'],
            $this->cache->getMulti(['bar', 'foo'])
        );
        self::assertSame(
            ['foo' => true, 'bar' => true],
            $this->cache->deleteMulti(['foo', 'bar'])
        );
        self::assertSame(
            ['foo' => null, 'bar' => null],
            $this->cache->getMulti(['foo', 'bar'])
        );
    }

    public function testFlush() : void
    {
        self::assertSame(
            ['foo' => true, 'bar' => true],
            $this->cache->setMulti(['foo' => 'x', 'bar' => 'y'], 1)
        );
        self::assertSame(
            ['bar' => 'y', 'foo' => 'x'],
            $this->cache->getMulti(['bar', 'foo'])
        );
        self::assertTrue($this->cache->flush());
        self::assertSame(
            ['bar' => null, 'foo' => null],
            $this->cache->getMulti(['bar', 'foo'])
        );
    }

    public function testIncrement() : void
    {
        self::assertSame(1, $this->cache->increment('i'));
        self::assertSame(2, $this->cache->increment('i'));
        self::assertSame(5, $this->cache->increment('i', 3));
        self::assertSame(6, $this->cache->increment('i', 1, 2));
        \sleep(3);
        self::assertSame(1, $this->cache->increment('i'));
        self::assertSame(11, $this->cache->increment('i', 10));
    }

    public function testDecrement() : void
    {
        self::assertSame(-1, $this->cache->decrement('i'));
        self::assertSame(-2, $this->cache->decrement('i'));
        self::assertSame(-5, $this->cache->decrement('i', 3));
        self::assertSame(-6, $this->cache->decrement('i', 1, 2));
        \sleep(3);
        self::assertSame(-1, $this->cache->decrement('i'));
        self::assertSame(-11, $this->cache->decrement('i', 10));
    }

    public function testIncrementAndDecrement() : void
    {
        self::assertSame(1, $this->cache->increment('id'));
        self::assertSame(2, $this->cache->increment('id'));
        self::assertSame(3, $this->cache->increment('id'));
        self::assertSame(2, $this->cache->decrement('id'));
        self::assertSame(0, $this->cache->decrement('id', 2));
    }
}
