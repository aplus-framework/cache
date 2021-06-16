<?php namespace Tests\Cache;

use Framework\Cache\Cache;

final class MemcachedCacheMsgpackTest extends MemcachedCacheTest
{
	protected string $serializer = Cache::SERIALIZER_MSGPACK;
}
