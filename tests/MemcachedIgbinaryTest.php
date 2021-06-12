<?php namespace Tests\Cache;

use Framework\Cache\Cache;

final class MemcachedIgbinaryTest extends MemcachedTest
{
	protected string $serializer = Cache::SERIALIZER_IGBINARY;
}
