<?php
/*
 * This file is part of Aplus Framework Cache Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Cache;

use Framework\Cache\Cache;
use Framework\Cache\Debug\CacheCollector;
use Framework\Cache\FilesCache;
use Framework\Cache\MemcachedCache;
use Framework\Cache\RedisCache;

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

    protected function setCollector() : CacheCollector
    {
        $collector = new CacheCollector();
        $this->cache->setDebugCollector($collector);
        return $collector;
    }

    public function testDebugCacheNotSet() : void
    {
        $collector = new CacheCollector();
        self::assertStringContainsString(
            'This collector has not been added to a Cache instance',
            $collector->getContents()
        );
    }

    public function testDebugActivities() : void
    {
        $collector = $this->setCollector();
        self::assertEmpty($collector->getActivities());
        $this->cache->get('foo');
        self::assertSame(
            [
                'collector',
                'class',
                'description',
                'start',
                'end',
            ],
            \array_keys($collector->getActivities()[0])
        );
    }

    public function testDebugDefault() : void
    {
        $collector = $this->setCollector();
        self::assertStringContainsString(
            $this->serializer,
            $collector->getContents()
        );
        self::assertStringContainsString(
            'No command was run',
            $collector->getContents()
        );
    }

    public function testDebugRunCommands() : void
    {
        $collector = $this->setCollector();
        $this->cache->get('foo');
        $contents = $collector->getContents();
        self::assertStringContainsString('Ran 1 command', $contents);
        self::assertStringContainsString('GET', $contents);
        $this->cache->set('xxx', 'foo', 1);
        $contents = $collector->getContents();
        self::assertStringContainsString('Ran 2 commands', $contents);
        self::assertStringContainsString('SET', $contents);
        $this->cache->delete('xxx');
        $contents = $collector->getContents();
        self::assertStringContainsString('Ran 3 commands', $contents);
        self::assertStringContainsString('DELETE', $contents);
        $this->cache->flush();
        $contents = $collector->getContents();
        self::assertStringContainsString('Ran 4 commands', $contents);
        self::assertStringContainsString('FLUSH', $contents);
    }

    public function testDebugHandler() : void
    {
        $collector = new class() extends CacheCollector {
            public function getHandler() : string
            {
                return parent::getHandler();
            }
        };
        $this->cache->setDebugCollector($collector);
        // @phpstan-ignore-next-line
        $handler = match ($this->cache::class) {
            FilesCache::class => 'files',
            MemcachedCache::class => 'memcached',
            RedisCache::class => 'redis',
        };
        self::assertSame($handler, $collector->getHandler());
        $cache = new class() extends FilesCache {
            protected string $serializer = Cache::SERIALIZER_PHP;

            public function __construct()
            {
            }

            public function __destruct()
            {
            }
        };
        $cache->setDebugCollector($collector);
        self::assertStringContainsString('@anonymous', $collector->getHandler());
    }
}
