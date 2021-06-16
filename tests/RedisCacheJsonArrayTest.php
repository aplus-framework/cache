<?php namespace Tests\Cache;

use Framework\Cache\Cache;

final class RedisCacheJsonArrayTest extends RedisCacheTest
{
	protected string $serializer = Cache::SERIALIZER_JSON_ARRAY;
}
