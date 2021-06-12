<?php namespace Tests\Cache;

use Framework\Cache\Cache;

final class RedisJsonArrayTest extends RedisTest
{
	protected string $serializer = Cache::SERIALIZER_JSON_ARRAY;
}
