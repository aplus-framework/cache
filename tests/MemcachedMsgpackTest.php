<?php namespace Tests\Cache;

use Framework\Cache\Cache;

class MemcachedMsgpackTest extends MemcachedTest
{
	protected $serializer = Cache::SERIALIZER_MSGPACK;
}
