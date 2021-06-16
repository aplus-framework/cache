<?php namespace Tests\Cache;

use Framework\Cache\Cache;

final class MemcachedCacheJsonArrayTest extends MemcachedCacheTest
{
	protected string $serializer = Cache::SERIALIZER_JSON_ARRAY;
}
