<?php namespace Tests\Cache;

use Framework\Cache\Cache;

final class MemcachedMsgpackTest extends MemcachedTest
{
	protected string $serializer = Cache::SERIALIZER_MSGPACK;
}
