<?php namespace Tests\Cache;

use Framework\Cache\Cache;

final class MemcachedCacheJsonTest extends MemcachedCacheTest
{
	protected string $serializer = Cache::SERIALIZER_JSON;
}
