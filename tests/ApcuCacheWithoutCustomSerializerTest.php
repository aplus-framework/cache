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

final class ApcuCacheWithoutCustomSerializerTest extends ApcuCacheTest
{
    public function setUp() : void
    {
        $this->cache = new ApcuCache(
            [
                'use_custom_serializer' => false,
            ],
            $this->prefix,
            $this->serializer,
            $this->getLogger()
        );
    }

    public function testCustomSerializer() : void
    {
        // @phpstan-ignore-next-line
        self::assertFalse($this->cache->isUsingCustomSerializer());
    }
}
