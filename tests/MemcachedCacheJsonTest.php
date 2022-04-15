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

use Framework\Cache\Serializer;

final class MemcachedCacheJsonTest extends MemcachedCacheTest
{
    protected Serializer $serializer = Serializer::JSON;
}
