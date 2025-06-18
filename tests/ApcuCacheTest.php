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

use Framework\Cache\ApcuCache;

class ApcuCacheTest extends TestCase
{
    public function setUp() : void
    {
        if (!\extension_loaded('apcu')) {
            throw new \RuntimeException('APCu extension is not loaded');
        }
        $this->cache = new ApcuCache(
            $this->configs,
            $this->prefix,
            $this->serializer,
            $this->getLogger()
        );
    }

    public function testSerializer() : void
    {
        $this->cache = new ApcuCache(
            $this->configs,
            $this->prefix,
            $this->serializer->value,
            $this->getLogger()
        );
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage(
            '"foo" is not a valid backing value for enum Framework\Cache\Serializer'
        );
        $this->cache = new ApcuCache(
            $this->configs,
            $this->prefix,
            'foo',
            $this->getLogger()
        );
    }

    public function testDefaultConfigs() : void
    {
        self::assertInstanceOf(ApcuCache::class, new ApcuCache());
    }
}
