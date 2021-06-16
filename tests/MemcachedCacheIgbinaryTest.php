<?php namespace Tests\Cache;

use Framework\Cache\Cache;

final class MemcachedCacheIgbinaryTest extends MemcachedCacheTest
{
	protected string $serializer = Cache::SERIALIZER_IGBINARY;
}
