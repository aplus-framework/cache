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
}
