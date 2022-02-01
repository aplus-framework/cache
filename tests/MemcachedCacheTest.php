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
}
