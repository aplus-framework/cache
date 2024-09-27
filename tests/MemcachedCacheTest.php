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
        $this->cache = new MemcachedCache(
            $this->configs,
            $this->prefix,
            $this->serializer,
            $this->getLogger()
        );
    }

    public function testSerializer() : void
    {
        $this->cache = new MemcachedCache(
            $this->configs,
            $this->prefix,
            $this->serializer->value,
            $this->getLogger()
        );
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage(
            '"foo" is not a valid backing value for enum Framework\Cache\Serializer'
        );
        $this->cache = new MemcachedCache(
            $this->configs,
            $this->prefix,
            'foo',
            $this->getLogger()
        );
    }

    public function testCustomInstance() : void
    {
        $cache = new MemcachedCache(null);
        self::assertNull($cache->getMemcached());
        $memcached = new \Memcached();
        $cache->setMemcached($memcached);
        self::assertSame($memcached, $cache->getMemcached());
    }

    public function testCustomInstanceConstructor() : void
    {
        $memcached = new \Memcached();
        $cache = new MemcachedCache($memcached);
        self::assertSame($memcached, $cache->getMemcached());
    }
}
