<?php namespace Tests\Cache;

use Framework\Cache\Cache;

final class RedisMsgpackTest extends RedisTest
{
	protected string $serializer = Cache::SERIALIZER_MSGPACK;
}
