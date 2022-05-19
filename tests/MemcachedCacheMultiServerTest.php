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

    public function testHostInPool() : void
    {
        $configs = [
            'servers' => [
                [
                    'host' => \getenv('MEMCACHED_HOST'),
                ],
                [
                    'host' => \getenv('MEMCACHED_HOST'),
                ],
            ],
        ];
        new MemcachedCache(
            $configs,
            $this->prefix,
            $this->serializer,
            $this->getLogger()
        );
        self::assertSame(
            'Cache (memcached): Server pool already has '
            . \getenv('MEMCACHED_HOST') . ':11211',
            $this->getLogger()->getLastLog()->message
        );
    }

    public function testCouldNotConnect() : void
    {
        $configs = [
            'servers' => [
                [
                    'host' => '192.168.0.1',
                ],
            ],
        ];
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cache (memcached): Could not connect to any server');
        new MemcachedCache(
            $configs,
            $this->prefix,
            $this->serializer,
            $this->getLogger()
        );
    }
}
