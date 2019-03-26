<?php namespace Tests\Cache;

use Framework\Cache\Cache;

class RedisJsonTest extends RedisTest
{
	protected $serializer = Cache::SERIALIZER_JSON;
}
