<?php namespace Tests\Cache;

use Framework\Cache\Cache;

final class RedisCacheMsgpackTest extends RedisCacheTest
{
	protected string $serializer = Cache::SERIALIZER_MSGPACK;
}
