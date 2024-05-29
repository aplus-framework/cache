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

use PHPUnit\Framework\TestCase;

final class CacheTest extends TestCase
{
    protected CacheMock $cache;

    protected function setUp() : void
    {
        $this->cache = new CacheMock();
    }

    public function testDefaultTtl() : void
    {
        self::assertSame(60, $this->cache->getDefaultTtl());
        $this->cache->setDefaultTtl(15);
        self::assertSame(15, $this->cache->getDefaultTtl());
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Default TTL must be greater than 0. 0 given'
        );
        $this->cache->setDefaultTtl(0);
    }
}
