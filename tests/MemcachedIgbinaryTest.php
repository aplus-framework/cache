<?php namespace Tests\Cache;

use Framework\Cache\Cache;

class MemcachedIgbinaryTest extends MemcachedTest
{
	protected string $serializer = Cache::SERIALIZER_IGBINARY;
}
