<?php namespace Tests\Cache;

use Framework\Cache\Cache;

class RedisJsonTest extends RedisTest
{
	protected string $serializer = Cache::SERIALIZER_JSON;
}
