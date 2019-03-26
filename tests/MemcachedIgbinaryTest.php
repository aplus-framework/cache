<?php namespace Tests\Cache;

use Framework\Cache\Cache;

class MemcachedIgbinaryTest extends MemcachedTest
{
	protected $serializer = Cache::SERIALIZER_IGBINARY;
}
