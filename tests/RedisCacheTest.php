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

use Framework\Cache\RedisCache;

class RedisCacheTest extends TestCase
{
    public function setUp() : void
    {
        $this->configs = [
            'host' => \getenv('REDIS_HOST'),
        ];
        $this->setCache();
    }

    protected function setCache() : void
    {
        $this->cache = new RedisCache(
            $this->configs,
            $this->prefix,
            $this->serializer,
            $this->getLogger()
        );
    }

    public function testAuth() : void
    {
        $this->configs = [
            'host' => \getenv('REDIS_HOST'),
            'password' => 'foo',
        ];
        $this->expectException(\RedisException::class);
        $this->setCache();
    }

    public function testSelect() : void
    {
        $this->configs = [
            'host' => \getenv('REDIS_HOST'),
            'database' => 0,
        ];
        $this->setCache();
        self::assertInstanceOf(RedisCache::class, $this->cache);
    }
}
