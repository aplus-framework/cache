<?php namespace Tests\Cache;

use Framework\Cache\Cache;

class RedisJsonArrayTest extends RedisTest
{
	protected $serializer = Cache::SERIALIZER_JSON_ARRAY;
}
